<?php

namespace App\Http\Controllers\Api\V1\Public;

use App\Models\Lab\Experiment;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Http\Request;

/**
 * Laboratoire : expérimentations publiées (D11). demo_component_key désigne un composant de
 * démonstration de la liste blanche du frontend ; aucun code n'est exécuté depuis l'API.
 * Filtres : category, technology, kind, featured.
 */
class ExperimentController extends PublishedContentController
{
    protected function model(): string
    {
        return Experiment::class;
    }

    protected function listRelations(): array
    {
        return [
            'category.translations',
            'technologies' => self::visible(),
        ];
    }

    protected function detailRelations(): array
    {
        return ['gallery.translations'];
    }

    protected function applyFilters(Builder $query, Request $request): void
    {
        self::filterByCategory($query, $request);
        self::filterByTechnology($query, $request);
        self::filterBySearch($query, $request);

        if ($request->filled('kind')) {
            $query->where('kind', $request->string('kind')->toString());
        }
    }
}
