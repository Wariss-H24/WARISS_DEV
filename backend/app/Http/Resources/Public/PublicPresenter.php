<?php

namespace App\Http\Resources\Public;

use App\Enums\MediaVisibility;
use App\Models\Career\Education;
use App\Models\Media\Media;
use App\Models\Portfolio\Project;
use App\Services\Media\MediaStorage;
use Astrotomic\Translatable\Contracts\Translatable;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Database\Eloquent\Model;

/**
 * Représentation publique d'un contenu, dans une seule langue.
 *
 * Règles de sécurité, appliquées à tous les contenus :
 * - aucun champ interne (statut, auteur, dates techniques, visibilité, consentement…) ;
 * - un média n'est exposé que s'il est public (ou externe) : un média privé devient null ;
 * - projet confidentiel : client et liens masqués ; formation : justificatif jamais exposé.
 *
 * Champs traduits : ceux de la langue demandée, sinon de la langue par défaut (« locale » indique
 * la langue réellement servie) ; « slugs » donne le slug de chaque langue (sélecteur de langue, hreflang).
 */
class PublicPresenter
{
    /**
     * @var list<string>
     */
    private const INTERNAL_FIELDS = [
        'status', 'created_at', 'updated_at', 'deleted_at', 'created_by', 'author_id', 'uploaded_by',
        'is_visible', 'is_active', 'consent_given', 'position', 'user_id', 'pivot', 'laravel_through_key',
    ];

    /**
     * @var list<string>
     */
    private const TRANSLATION_INTERNAL_FIELDS = ['id', 'locale', 'created_at', 'updated_at', 'search_vector'];

    /**
     * @var list<string>
     */
    private const CONFIDENTIAL_PROJECT_FIELDS = ['client_name', 'live_url', 'repository_url', 'demo_url'];

    /**
     * Médias déjà chargés, par identifiant.
     *
     * @var array<int, Media|null>
     */
    private array $media = [];

    public function __construct(private readonly MediaStorage $storage) {}

    /**
     * @param  iterable<Model>  $models
     * @return list<array<string, mixed>>
     */
    public function many(iterable $models): array
    {
        $models = collect($models)->all();
        $this->preloadMedia($models);

        return array_values(array_map(fn (Model $model): array => $this->present($model), $models));
    }

    /**
     * @return array<string, mixed>
     */
    public function one(Model $model): array
    {
        $this->preloadMedia([$model]);

        return $this->present($model);
    }

    /**
     * Média public : URL et textes alternatifs ; null pour un média privé ou absent.
     *
     * @return array<string, mixed>|null
     */
    public function media(?Media $media): ?array
    {
        if ($media === null || ($media->visibility !== MediaVisibility::Public && ! $media->isExternal())) {
            return null;
        }

        $translation = $media->translate(app()->getLocale(), true);

        return [
            'id' => $media->id,
            'kind' => $media->kind->value,
            'url' => $this->storage->url($media),
            'mime_type' => $media->mime_type,
            'width' => $media->width,
            'height' => $media->height,
            'duration_seconds' => $media->duration_seconds,
            'alt' => $translation?->getAttribute('alt_text'),
            'caption' => $translation?->getAttribute('caption'),
            'credit' => $media->credit,
        ];
    }

    /**
     * @return array<string, mixed>
     */
    private function present(Model $model): array
    {
        if ($model instanceof Media) {
            return $this->media($model) ?? [];
        }

        $payload = array_diff_key($model->attributesToArray(), array_flip(self::INTERNAL_FIELDS));

        if ($model instanceof Project && $model->is_confidential) {
            $payload = [...$payload, ...array_fill_keys(self::CONFIDENTIAL_PROJECT_FIELDS, null)];
        }

        if ($model instanceof Education) {
            unset($payload['proof_media_id']);
        }

        foreach ($payload as $field => $value) {
            if (self::isMediaReference($field)) {
                unset($payload[$field]);
                $payload[$field === 'media_id' ? 'media' : substr($field, 0, -strlen('_media_id'))] = is_int($value) ? $this->media($this->media[$value] ?? null) : null;
            }
        }

        if ($model instanceof Translatable) {
            $payload = [...$payload, ...$this->translation($model)];
        }

        foreach ($model->getRelations() as $name => $related) {
            if (in_array($name, ['translations', 'translation', 'pivot'], true)) {
                continue;
            }

            $payload[str($name)->snake()->toString()] = match (true) {
                $related instanceof Collection => array_values(array_filter(
                    $related->map(fn (Model $item): array => $this->present($item))->all(),
                    fn (array $item): bool => $item !== [],
                )),
                $related instanceof Model => $this->present($related) ?: null,
                default => null,
            };
        }

        return $payload;
    }

    /**
     * @return array<string, mixed>
     */
    private function translation(Model&Translatable $model): array
    {
        $translation = $model->translate(app()->getLocale(), true);

        if ($translation === null) {
            return [];
        }

        $foreignKey = $model->translations()->getForeignKeyName();
        $fields = array_diff_key($translation->attributesToArray(), array_flip([...self::TRANSLATION_INTERNAL_FIELDS, $foreignKey]));
        $fields['locale'] = $translation->getAttribute('locale');

        if (array_key_exists('slug', $fields)) {
            /** @var iterable<Model> $translations */
            $translations = $model->getRelationValue('translations');
            $fields['slugs'] = collect($translations)
                ->mapWithKeys(fn (Model $item): array => [(string) $item->getAttribute('locale') => $item->getAttribute('slug')])
                ->all();
        }

        return $fields;
    }

    /**
     * Colonne qui référence un média : media_id ou <rôle>_media_id (cover_media_id, photo_media_id…).
     */
    private static function isMediaReference(string $field): bool
    {
        return $field === 'media_id' || str_ends_with($field, '_media_id');
    }

    /**
     * Charge en une requête tous les médias référencés par les contenus et leurs relations.
     *
     * @param  array<int, Model>  $models
     */
    private function preloadMedia(array $models): void
    {
        $ids = [];

        foreach ($models as $model) {
            $this->collectMediaIds($model, $ids);
        }

        $missing = array_diff(array_keys($ids), array_keys($this->media));

        if ($missing === []) {
            return;
        }

        $found = Media::query()->with('translations')->whereKey($missing)->get()->keyBy('id');

        foreach ($missing as $id) {
            $this->media[$id] = $found->get($id);
        }
    }

    /**
     * @param  array<int, true>  $ids
     */
    private function collectMediaIds(Model $model, array &$ids): void
    {
        foreach ($model->getAttributes() as $field => $value) {
            if (self::isMediaReference($field) && is_numeric($value)) {
                $ids[(int) $value] = true;
            }
        }

        foreach ($model->getRelations() as $related) {
            foreach ($related instanceof Collection ? $related : [$related] as $item) {
                if ($item instanceof Model && ! $item instanceof Media) {
                    $this->collectMediaIds($item, $ids);
                }
            }
        }
    }
}
