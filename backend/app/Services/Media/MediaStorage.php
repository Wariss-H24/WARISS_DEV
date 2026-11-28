<?php

namespace App\Services\Media;

use App\Enums\MediaKind;
use App\Enums\MediaVisibility;
use App\Models\Media\Media;
use App\Models\User;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use Intervention\Image\Drivers\Gd\Driver as GdDriver;
use Intervention\Image\Encoders\JpegEncoder;
use Intervention\Image\Encoders\PngEncoder;
use Intervention\Image\Encoders\WebpEncoder;
use Intervention\Image\ImageManager;
use Throwable;

/**
 * Stockage des fichiers de la bibliothèque de médias (D14).
 *
 * - Nom de fichier aléatoire (UUID) : le nom d'origine n'est jamais utilisé comme chemin.
 * - Images ré-encodées par GD : orientation appliquée, données EXIF (GPS, appareil) supprimées.
 * - Disque « public » pour les médias publics, disque privé « local » sinon (D-13).
 * - Empreinte SHA-256 du fichier envoyé, pour détecter les doublons.
 */
class MediaStorage
{
    /**
     * Empreinte du fichier envoyé (avant ré-encodage), utilisée pour détecter les doublons.
     */
    public function checksum(UploadedFile $file): string
    {
        return (string) hash_file('sha256', (string) $file->getRealPath());
    }

    /**
     * Enregistre un fichier déjà validé (extension et type MIME réel de la liste blanche).
     *
     * @param  array<string, mixed>  $attributes  champs éditables (collection, crédit, traductions…)
     */
    public function store(UploadedFile $file, MediaVisibility $visibility, ?MediaKind $kind, array $attributes, ?User $uploader): Media
    {
        $extension = strtolower($file->getClientOriginalExtension());
        /** @var array{mimes: list<string>, kind: string, max_kb: int} $type */
        $type = config("wariss.uploads.types.{$extension}");
        $disk = $this->diskFor($visibility);
        $storedName = Str::uuid()->toString().'.'.$extension;
        $path = trim((string) config('wariss.uploads.directory'), '/').'/'.now()->format('Y/m').'/'.$storedName;

        $width = null;
        $height = null;

        if ($type['kind'] === MediaKind::Image->value) {
            [$contents, $width, $height] = $this->sanitizeImage($file, $extension);
            Storage::disk($disk)->put($path, $contents);
            $size = strlen($contents);
        } else {
            Storage::disk($disk)->putFileAs(dirname($path), $file, $storedName);
            $size = (int) $file->getSize();
        }

        try {
            return DB::transaction(function () use ($file, $visibility, $kind, $attributes, $uploader, $extension, $type, $disk, $path, $storedName, $size, $width, $height): Media {
                $media = new Media;
                $media->fill(array_diff_key($attributes, ['translations' => true]));
                $media->forceFill([
                    'disk' => $disk,
                    'path' => $path,
                    'original_name' => Str::limit(basename($file->getClientOriginalName()), 250, ''),
                    'stored_name' => $storedName,
                    'mime_type' => $type['mimes'][0],
                    'extension' => $extension,
                    'size_bytes' => $size,
                    'checksum_sha256' => $this->checksum($file),
                    'width' => $width,
                    'height' => $height,
                    'kind' => $kind ?? MediaKind::from($type['kind']),
                    'visibility' => $visibility,
                    'uploaded_by' => $uploader?->getKey(),
                ]);

                foreach ($attributes['translations'] ?? [] as $locale => $translation) {
                    $media->translateOrNew($locale)->fill($translation);
                }

                $media->save();

                return $media;
            });
        } catch (Throwable $exception) {
            Storage::disk($disk)->delete($path);

            throw $exception;
        }
    }

    /**
     * Déplace le fichier d'un média vers le disque correspondant à sa nouvelle visibilité.
     */
    public function changeVisibility(Media $media, MediaVisibility $visibility): void
    {
        $targetDisk = $this->diskFor($visibility);

        if ($media->isExternal() || $media->disk === $targetDisk) {
            $media->visibility = $visibility;
            $media->save();

            return;
        }

        $sourceDisk = (string) $media->disk;
        $path = (string) $media->path;
        $stream = Storage::disk($sourceDisk)->readStream($path);
        Storage::disk($targetDisk)->writeStream($path, $stream);

        if (is_resource($stream)) {
            fclose($stream);
        }

        try {
            $media->forceFill(['disk' => $targetDisk, 'visibility' => $visibility])->save();
        } catch (Throwable $exception) {
            Storage::disk($targetDisk)->delete($path);

            throw $exception;
        }

        Storage::disk($sourceDisk)->delete($path);
    }

    /**
     * Supprime le fichier d'un média (après la suppression de sa ligne).
     */
    public function deleteFile(Media $media): void
    {
        if (! $media->isExternal() && $media->disk !== null && $media->path !== null) {
            Storage::disk($media->disk)->delete($media->path);
        }
    }

    /**
     * URL d'affichage : publique pour un fichier public, route authentifiée de l'administration sinon.
     */
    public function url(Media $media): ?string
    {
        return match (true) {
            $media->isExternal() => $media->external_url,
            $media->visibility === MediaVisibility::Public => Storage::disk((string) $media->disk)->url((string) $media->path),
            default => route('admin.media.file', ['id' => $media->getKey()]),
        };
    }

    public function diskFor(MediaVisibility $visibility): string
    {
        return $visibility === MediaVisibility::Public ? 'public' : 'local';
    }

    /**
     * Ré-encode une image dans son format : orientation appliquée puis métadonnées retirées.
     *
     * @return array{0: string, 1: int, 2: int}
     */
    private function sanitizeImage(UploadedFile $file, string $extension): array
    {
        $quality = (int) config('wariss.uploads.image_quality');
        $image = ImageManager::usingDriver(GdDriver::class, autoOrientation: true, strip: true)
            ->decodePath((string) $file->getRealPath());

        $encoder = match ($extension) {
            'png' => new PngEncoder(strip: true),
            'webp' => new WebpEncoder(quality: $quality, strip: true),
            default => new JpegEncoder(quality: $quality, progressive: true, strip: true),
        };

        return [$image->encode($encoder)->toString(), $image->width(), $image->height()];
    }
}
