<?php

namespace App\Http\Resources\Admin;

use Astrotomic\Translatable\Contracts\Translatable;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\MorphToMany;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

/**
 * Représentation d'un contenu pour l'administration : tous ses champs (sauf les champs masqués
 * du modèle), toutes ses traductions par langue (pour remplir les onglets FR / EN) et ses relations
 * chargées — identifiants pour les relations plusieurs-à-plusieurs, objets complets pour les
 * éléments enfants.
 */
class AdminResource extends JsonResource
{
    /**
     * Champs techniques des tables de traduction, jamais renvoyés.
     *
     * @var list<string>
     */
    private const TRANSLATION_INTERNAL_FIELDS = ['id', 'locale', 'created_at', 'updated_at', 'search_vector'];

    /**
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        /** @var Model $model */
        $model = $this->resource;

        return self::payload($model);
    }

    /**
     * @return array<string, mixed>
     */
    public static function payload(Model $model): array
    {
        $payload = $model->attributesToArray();

        if ($model instanceof Translatable && $model->relationLoaded('translations')) {
            $payload['translations'] = self::translations($model);
        }

        foreach ($model->getRelations() as $name => $related) {
            if ($name === 'translations' || $name === 'translation') {
                continue;
            }

            $relation = method_exists($model, $name) ? $model->{$name}() : null;

            if ($related instanceof Collection && $relation instanceof BelongsToMany && ! $relation instanceof MorphToMany) {
                $payload[str($name)->singular()->snake()->append('_ids')->toString()] = $related->modelKeys();

                continue;
            }

            $payload[str($name)->snake()->toString()] = match (true) {
                $related instanceof Collection => $related->map(fn (Model $item): array => self::payload($item))->all(),
                $related instanceof Model => self::payload($related),
                default => null,
            };
        }

        return $payload;
    }

    /**
     * @return array<string, array<string, mixed>>
     */
    private static function translations(Model&Translatable $model): array
    {
        $foreignKey = $model->translations()->getForeignKeyName();
        $byLocale = [];

        /** @var iterable<Model> $translations */
        $translations = $model->getRelation('translations');

        foreach ($translations as $translation) {
            $byLocale[(string) $translation->getAttribute('locale')] = array_diff_key(
                $translation->attributesToArray(),
                array_flip([...self::TRANSLATION_INTERNAL_FIELDS, $foreignKey]),
            );
        }

        return $byLocale;
    }
}
