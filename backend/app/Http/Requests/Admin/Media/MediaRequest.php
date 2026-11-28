<?php

namespace App\Http\Requests\Admin\Media;

use App\Enums\MediaKind;
use App\Enums\MediaVisibility;
use App\Http\Requests\Admin\AdminFormRequest;
use App\Models\Media\Media;
use App\Rules\AllowedUpload;
use Closure;
use Illuminate\Http\UploadedFile;
use Illuminate\Validation\Rule;
use Illuminate\Validation\Validator;

/**
 * Média de la bibliothèque (D14).
 *
 * Création : soit un fichier (« file », multipart), soit une ressource externe (« external_url »).
 * Le fichier est contrôlé sur son contenu réel, pas sur son nom : extension de la liste blanche
 * (config wariss.uploads), type MIME détecté, taille et nombre de pixels maximum.
 * Modification : métadonnées, traductions et visibilité ; le fichier lui-même ne change jamais
 * (on téléverse un nouveau média).
 */
class MediaRequest extends AdminFormRequest
{
    /**
     * @return array<string, array<int, mixed>>
     */
    public function rules(): array
    {
        /** @var list<string> $locales */
        $locales = config('translatable.locales');

        return [
            'visibility' => ['sometimes', Rule::enum(MediaVisibility::class)],
            'kind' => ['sometimes', Rule::enum(MediaKind::class)],
            'media_collection_id' => ['nullable', 'integer', Rule::exists('media_collections', 'id')],
            'credit' => ['nullable', 'string', 'max:255'],
            'duration_seconds' => ['nullable', 'integer', 'min:0', 'max:86400'],
            'translations' => ['sometimes', 'array:'.implode(',', $locales)],
            'translations.*' => ['array'],
            'translations.*.alt_text' => ['nullable', 'string', 'max:255'],
            'translations.*.caption' => ['nullable', 'string', 'max:255'],
            'translations.*.description' => ['nullable', 'string', 'max:5000'],
            ...($this->isCreating() ? $this->creationRules() : $this->updateRules()),
        ];
    }

    /**
     * La nature déclarée doit correspondre au fichier : une image peut être classée « icon »,
     * un PDF reste un document, une vidéo reste une vidéo.
     *
     * @return list<Closure(Validator): void>
     */
    public function after(): array
    {
        return [
            function (Validator $validator): void {
                if (! $this->filled('kind') || $validator->errors()->isNotEmpty()) {
                    return;
                }

                $allowed = $this->allowedKinds();

                if ($allowed !== null && ! in_array($this->input('kind'), $allowed, true)) {
                    $validator->errors()->add('kind', __('La nature choisie ne correspond pas au fichier (valeurs possibles : :kinds).', ['kinds' => implode(', ', $allowed)]));
                }
            },
        ];
    }

    /**
     * @return array<string, array<int, mixed>>
     */
    private function creationRules(): array
    {
        return [
            'file' => ['required_without:external_url', 'prohibits:external_url', 'file', new AllowedUpload],
            'external_url' => ['required_without:file', 'string', 'max:2048', 'url:https'],
            'kind' => ['required_with:external_url', Rule::enum(MediaKind::class)],
            'allow_duplicate' => ['sometimes', 'boolean'],
        ];
    }

    /**
     * @return array<string, array<int, mixed>>
     */
    private function updateRules(): array
    {
        $isExternal = $this->media()?->isExternal() ?? false;

        return [
            'file' => ['prohibited'],
            'external_url' => $isExternal ? ['sometimes', 'required', 'string', 'max:2048', 'url:https'] : ['prohibited'],
        ];
    }

    /**
     * Natures possibles pour le fichier concerné ; null si toutes le sont (média externe).
     *
     * @return list<string>|null
     */
    private function allowedKinds(): ?array
    {
        $extension = match (true) {
            $this->file('file') instanceof UploadedFile => strtolower($this->file('file')->getClientOriginalExtension()),
            $this->isCreating() => null,
            default => $this->media()?->extension,
        };

        if ($extension === null) {
            return null;
        }

        $kind = (string) config("wariss.uploads.types.{$extension}.kind", MediaKind::Other->value);

        return $kind === MediaKind::Image->value ? [MediaKind::Image->value, MediaKind::Icon->value] : [$kind];
    }

    private function media(): ?Media
    {
        $id = $this->routeId();

        return $id === null ? null : Media::query()->find($id);
    }
}
