<?php

namespace App\Rules;

use App\Enums\MediaKind;
use Closure;
use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Http\UploadedFile;

/**
 * Fichier accepté par la bibliothèque de médias (D14), contrôlé sur son contenu et non sur son nom :
 * extension de la liste blanche (config wariss.uploads.types), type MIME réel détecté,
 * taille maximale du format et, pour une image, nombre de pixels maximum.
 */
class AllowedUpload implements ValidationRule
{
    /**
     * @param  list<string>|null  $extensions  restriction supplémentaire (ex. ['pdf'] pour un CV)
     */
    public function __construct(private readonly ?array $extensions = null) {}

    public function validate(string $attribute, mixed $value, Closure $fail): void
    {
        if (! $value instanceof UploadedFile || ! $value->isValid()) {
            return;
        }

        /** @var array<string, array{mimes: list<string>, kind: string, max_kb: int}> $types */
        $types = config('wariss.uploads.types');

        if ($this->extensions !== null) {
            $types = array_intersect_key($types, array_flip($this->extensions));
        }

        $type = $types[strtolower($value->getClientOriginalExtension())] ?? null;

        if ($type === null) {
            $fail(__('Ce type de fichier n\'est pas accepté. Formats acceptés : :formats.', ['formats' => implode(', ', array_keys($types))]));

            return;
        }

        if (! in_array($value->getMimeType(), $type['mimes'], true)) {
            $fail(__('Le contenu du fichier ne correspond pas à son extension.'));

            return;
        }

        if ((int) $value->getSize() > $type['max_kb'] * 1024) {
            $fail(__('Le fichier dépasse la taille maximale de :size Mo pour ce format.', ['size' => intdiv($type['max_kb'], 1024)]));

            return;
        }

        if ($type['kind'] === MediaKind::Image->value) {
            $dimensions = @getimagesize((string) $value->getRealPath());

            if ($dimensions === false) {
                $fail(__('L\'image est illisible ou corrompue.'));
            } elseif ((int) config('wariss.uploads.max_image_pixels') < $dimensions[0] * $dimensions[1]) {
                $fail(__('L\'image est trop grande : réduisez ses dimensions avant de l\'envoyer.'));
            }
        }
    }
}
