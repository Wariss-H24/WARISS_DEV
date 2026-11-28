<?php

namespace App\Http\Controllers\Api\V1\Admin\Media;

use App\Actions\Admin\SaveModel;
use App\Enums\ActivityEvent;
use App\Enums\MediaKind;
use App\Enums\MediaVisibility;
use App\Http\Controllers\Api\V1\Admin\AdminCrudController;
use App\Http\Requests\Admin\Media\MediaRequest;
use App\Http\Resources\Admin\MediaResource;
use App\Models\Media\Media;
use App\Models\User;
use App\Services\ActivityLogger;
use App\Services\Media\MediaStorage;
use App\Services\Media\MediaUsages;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Resources\Json\JsonResource;
use Illuminate\Http\Response;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Arr;
use Illuminate\Support\Facades\Storage;
use Spatie\QueryBuilder\AllowedFilter;
use Symfony\Component\HttpFoundation\StreamedResponse;

/**
 * Bibliothèque de médias (D14).
 *
 * - Création : téléversement d'un fichier (doublon refusé en 409, sauf allow_duplicate) ou média externe.
 * - Modification : métadonnées et traductions ; un changement de visibilité déplace le fichier
 *   entre le disque public et le disque privé (D-13).
 * - Suppression : 409 avec la liste des usages si le média est encore utilisé ; une confirmation
 *   (confirm=1) vide les emplacements facultatifs, mais un usage bloquant (galerie, version de CV)
 *   doit d'abord être retiré.
 * - Fichier : servi par une route authentifiée, y compris pour un média privé.
 */
class MediaController extends AdminCrudController
{
    public function __construct(
        private readonly MediaStorage $storage,
        private readonly MediaUsages $usages,
        private readonly ActivityLogger $activityLogger,
    ) {}

    protected function model(): string
    {
        return Media::class;
    }

    protected function formRequest(): string
    {
        return MediaRequest::class;
    }

    protected function resourceClass(): string
    {
        return MediaResource::class;
    }

    protected function filters(): array
    {
        return [
            AllowedFilter::exact('kind'),
            AllowedFilter::exact('visibility'),
            AllowedFilter::exact('media_collection_id'),
            AllowedFilter::partial('original_name'),
        ];
    }

    protected function sorts(): array
    {
        return ['id', 'created_at', 'updated_at', 'size_bytes', 'original_name'];
    }

    public function show(int $id): JsonResource
    {
        /** @var Media $media */
        $media = $this->find($id);

        return (new MediaResource($media))->withUsages($this->usages->for($media));
    }

    public function store(SaveModel $saveModel): JsonResponse
    {
        $request = app(MediaRequest::class);
        $data = $request->validated();
        $visibility = MediaVisibility::from($data['visibility'] ?? MediaVisibility::Private->value);
        $kind = isset($data['kind']) ? MediaKind::from($data['kind']) : null;
        $attributes = Arr::except($data, ['file', 'external_url', 'kind', 'visibility', 'allow_duplicate']);
        $file = $request->file('file');
        $user = $request->user() instanceof User ? $request->user() : null;

        if ($file instanceof UploadedFile) {
            $duplicateId = ($data['allow_duplicate'] ?? false)
                ? null
                : Media::query()->where('checksum_sha256', $this->storage->checksum($file))->value('id');

            if ($duplicateId !== null) {
                return response()->json([
                    'message' => __('Ce fichier existe déjà dans la bibliothèque.'),
                    'existing_id' => (int) $duplicateId,
                ], Response::HTTP_CONFLICT);
            }

            $media = $this->storage->store($file, $visibility, $kind, $attributes, $user);
            $this->activityLogger->log(ActivityEvent::Created, $media);
        } else {
            $media = $saveModel->handle(
                new Media,
                [...$attributes, 'external_url' => $data['external_url'], 'kind' => $kind, 'visibility' => $visibility],
                beforeSave: function (Model $model) use ($user): void {
                    $model->forceFill(['uploaded_by' => $user?->getKey()]);
                },
            );
        }

        return $this->show((int) $media->getKey())->response()->setStatusCode(Response::HTTP_CREATED);
    }

    public function update(int $id, SaveModel $saveModel): JsonResource
    {
        $data = $this->validated();
        /** @var Media $media */
        $media = $this->find($id);

        $saveModel->handle($media, Arr::except($data, ['visibility']));

        if (isset($data['visibility']) && $media->visibility->value !== $data['visibility']) {
            $this->storage->changeVisibility($media, MediaVisibility::from($data['visibility']));
            $this->activityLogger->log(ActivityEvent::Updated, $media, ['visibility']);
        }

        return $this->show($id);
    }

    public function destroy(int $id, ActivityLogger $activityLogger): Response|JsonResponse
    {
        /** @var Media $media */
        $media = $this->find($id);
        $usages = $this->usages->for($media);

        if (array_filter($usages, fn (array $usage): bool => $usage['blocking']) !== []) {
            return response()->json([
                'message' => __('Ce média fait partie d\'une galerie, d\'une pièce jointe ou d\'une version de CV : retirez-le de ces contenus avant de le supprimer.'),
                'usages' => $usages,
            ], Response::HTTP_CONFLICT);
        }

        if ($usages !== [] && ! request()->boolean('confirm')) {
            return response()->json([
                'message' => __('Ce média est encore utilisé. Confirmez la suppression (confirm=1) : les emplacements concernés seront vidés.'),
                'usages' => $usages,
                'requires_confirmation' => true,
            ], Response::HTTP_CONFLICT);
        }

        $conflict = $this->deleteOrConflict(fn () => $media->delete());

        if ($conflict !== null) {
            return $conflict;
        }

        $this->storage->deleteFile($media);

        return $this->deleted($media, $activityLogger, null);
    }

    /**
     * Sert le fichier d'un média stocké (public ou privé) aux comptes de l'administration.
     */
    public function file(int $id): StreamedResponse
    {
        /** @var Media $media */
        $media = $this->find($id);
        $disk = (string) $media->disk;
        $path = (string) $media->path;

        abort_if($media->isExternal() || ! Storage::disk($disk)->exists($path), Response::HTTP_NOT_FOUND);

        return Storage::disk($disk)->response($path, (string) $media->original_name, [
            'Content-Type' => (string) $media->mime_type,
            'X-Content-Type-Options' => 'nosniff',
            'Cache-Control' => 'private, no-store',
        ]);
    }
}
