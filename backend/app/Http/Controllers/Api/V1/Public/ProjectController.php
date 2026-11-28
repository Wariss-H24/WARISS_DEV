<?php

namespace App\Http\Controllers\Api\V1\Public;

use App\Models\Portfolio\Project;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Http\Request;

/**
 * Projets publiés (D6). Filtres : category, technology, kind, featured.
 * Un projet confidentiel n'expose ni son client ni ses liens.
 */
class ProjectController extends PublishedContentController
{
    protected function model(): string
    {
        return Project::class;
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
        return [
            'features' => self::visible(),
            'skills' => self::visible(),
            'caseStudies' => self::publishedOnly(),
            'gallery.translations',
        ];
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
