<?php

namespace App\Http\Controllers\Api\V1\Public;

use App\Models\Portfolio\CaseStudy;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Http\Request;

/**
 * Études de cas publiées (D7). Le projet lié n'apparaît que s'il est lui-même publié.
 * Filtres : project (slug du projet), technology, featured.
 */
class CaseStudyController extends PublishedContentController
{
    protected function model(): string
    {
        return CaseStudy::class;
    }

    protected function listRelations(): array
    {
        return [
            'project' => self::publishedOnly(),
            'technologies' => self::visible(),
        ];
    }

    protected function detailRelations(): array
    {
        return [
            'sections.translations',
            'gallery.translations',
        ];
    }

    protected function applyFilters(Builder $query, Request $request): void
    {
        self::filterByTechnology($query, $request);

        if ($request->filled('project')) {
            $query->whereHas('project', fn (Builder $project) => $project->scopes(['published'])->whereTranslation('slug', $request->string('project')->toString()));
        }
    }
}
