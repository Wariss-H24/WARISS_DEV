<?php

namespace App\Actions\Admin;

use App\Enums\ActivityEvent;
use App\Enums\PublicationStatus;
use App\Models\Seo\Redirect;
use App\Services\ActivityLogger;
use App\Services\PublicPath;
use Astrotomic\Translatable\Contracts\Translatable;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\DB;

/**
 * Enregistre un contenu de l'administration en une seule transaction :
 * champs autorisés, traductions, relations plusieurs-à-plusieurs, éléments enfants,
 * redirection 301 automatique si le slug d'un contenu publié change (D-06),
 * et inscription au journal d'activité.
 */
class SaveModel
{
    public function __construct(
        private readonly ActivityLogger $activityLogger,
        private readonly PublicPath $publicPath,
    ) {}

    /**
     * @param  array<string, mixed>  $data  données validées
     * @param  array<string, string>  $relations  clé du formulaire => relation plusieurs-à-plusieurs à synchroniser
     * @param  array<string, string>  $children  clé du formulaire => relation hasMany remplacée intégralement
     * @param  (callable(Model, array<string, mixed>, bool): void)|null  $beforeSave  champs fixés par le serveur (auteur, statut…)
     */
    public function handle(Model $model, array $data, array $relations = [], array $children = [], ?callable $beforeSave = null): Model
    {
        return DB::transaction(function () use ($model, $data, $relations, $children, $beforeSave): Model {
            $isNew = ! $model->exists;
            $wasPublic = ! $isNew && $this->isPublic($model);
            $previousPaths = $wasPublic ? $this->publicPaths($model) : [];
            $previousStatus = $model->getAttribute('status');

            $model->fill(array_diff_key($data, ['translations' => true], $relations, $children));
            $translationsChanged = $this->fillTranslations($model, $data['translations'] ?? []);

            if ($beforeSave !== null) {
                $beforeSave($model, $data, $isNew);
            }

            $model->save();
            $changedFields = array_keys($model->getChanges());

            foreach ($relations as $inputKey => $relation) {
                if (array_key_exists($inputKey, $data)) {
                    $model->{$relation}()->sync($data[$inputKey]);
                }
            }

            foreach ($children as $inputKey => $relation) {
                if (array_key_exists($inputKey, $data)) {
                    $this->replaceChildren($model, $relation, $data[$inputKey]);
                }
            }

            $this->redirectChangedPaths($model, $previousPaths);
            $this->logActivity($model, $isNew, $previousStatus, [
                ...$changedFields,
                ...($translationsChanged ? ['translations'] : []),
                ...array_keys(array_intersect_key($data, $relations + $children)),
            ]);

            return $model;
        });
    }

    /**
     * @param  array<string, array<string, mixed>>  $translations
     */
    private function fillTranslations(Model $model, array $translations): bool
    {
        if (! $model instanceof Translatable) {
            return false;
        }

        $changed = false;

        foreach ($translations as $locale => $attributes) {
            $translation = $model->translateOrNew($locale);
            $translation->fill($attributes);
            $changed = $changed || $translation->isDirty() || ! $translation->exists;
        }

        return $changed;
    }

    /**
     * Remplace tous les éléments enfants (fonctionnalités, sections…) par ceux du formulaire.
     *
     * @param  list<array<string, mixed>>  $items
     */
    private function replaceChildren(Model $model, string $relation, array $items): void
    {
        $model->{$relation}()->get()->each->delete();

        foreach ($items as $position => $item) {
            $child = $model->{$relation}()->make();
            $child->fill(['position' => $position] + array_diff_key($item, ['translations' => true]));
            $this->fillTranslations($child, $item['translations'] ?? []);
            $child->save();
        }
    }

    /**
     * Crée une redirection 301 de l'ancien chemin vers le nouveau, pour chaque langue dont
     * le slug a changé, et fait pointer les redirections existantes directement vers le nouveau
     * chemin (pas de chaîne de redirections).
     *
     * @param  array<string, string>  $previousPaths
     */
    private function redirectChangedPaths(Model $model, array $previousPaths): void
    {
        foreach ($this->publicPaths($model->refresh()) as $locale => $newPath) {
            $oldPath = $previousPaths[$locale] ?? null;

            if ($oldPath === null || $oldPath === $newPath) {
                continue;
            }

            Redirect::where('to_path', $oldPath)->update(['to_path' => $newPath]);
            Redirect::where('from_path', $newPath)->delete();
            Redirect::updateOrCreate(['from_path' => $oldPath], ['to_path' => $newPath, 'status_code' => 301, 'is_active' => true]);
        }
    }

    /**
     * @return array<string, string>
     */
    private function publicPaths(Model $model): array
    {
        /** @var list<string> $locales */
        $locales = config('translatable.locales');
        $paths = [];

        foreach ($locales as $locale) {
            $path = $this->publicPath->for($model, $locale);

            if ($path !== null) {
                $paths[$locale] = $path;
            }
        }

        return $paths;
    }

    private function isPublic(Model $model): bool
    {
        return method_exists($model, 'isPubliclyVisible') && $model->isPubliclyVisible();
    }

    /**
     * @param  list<string>  $changedFields
     */
    private function logActivity(Model $model, bool $isNew, mixed $previousStatus, array $changedFields): void
    {
        $status = $model->getAttribute('status');
        $event = match (true) {
            $isNew => ActivityEvent::Created,
            $status === PublicationStatus::Published && $previousStatus !== PublicationStatus::Published => ActivityEvent::Published,
            $previousStatus === PublicationStatus::Published && $status !== PublicationStatus::Published => ActivityEvent::Unpublished,
            default => ActivityEvent::Updated,
        };

        $this->activityLogger->log($event, $model, array_values(array_unique($changedFields)));
    }
}
