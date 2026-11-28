<?php

namespace App\Http\Controllers\Api\V1\Public;

use App\Models\Blog\PostCategory;
use App\Models\Blog\Tag;
use App\Models\Catalog\SkillCategory;
use App\Models\Catalog\TechnologyCategory;
use App\Models\Lab\ExperimentCategory;
use App\Models\Portfolio\ProjectCategory;
use App\Models\Services\ServiceCategory;
use Illuminate\Http\JsonResponse;

/**
 * Catalogues publics : technologies et compétences visibles regroupées par catégorie active (D4, D5),
 * et listes de catégories et d'étiquettes servant aux filtres du site.
 */
class CatalogController extends PublicController
{
    /**
     * Taxonomies exposées : segment d'URL => modèle.
     *
     * @var array<string, class-string<ProjectCategory|ServiceCategory|ExperimentCategory|PostCategory|Tag>>
     */
    public const TAXONOMIES = [
        'project-categories' => ProjectCategory::class,
        'service-categories' => ServiceCategory::class,
        'experiment-categories' => ExperimentCategory::class,
        'post-categories' => PostCategory::class,
        'tags' => Tag::class,
    ];

    public function technologies(): JsonResponse
    {
        return $this->list(TechnologyCategory::query()
            ->where('is_active', true)
            ->orderBy('position')
            ->orderBy('id')
            ->with(['translations', 'technologies' => fn ($query) => $query->where('is_visible', true)->orderBy('position')->orderBy('id')->with('translations')])
            ->get()
            ->filter(fn (TechnologyCategory $category): bool => $category->technologies->isNotEmpty()));
    }

    public function skills(): JsonResponse
    {
        return $this->list(SkillCategory::query()
            ->where('is_active', true)
            ->orderBy('position')
            ->orderBy('id')
            ->with(['translations', 'skills' => fn ($query) => $query->where('is_visible', true)->orderBy('position')->orderBy('id')->with(['translations', 'technologies' => fn ($technologies) => $technologies->where('is_visible', true)])])
            ->get()
            ->filter(fn (SkillCategory $category): bool => $category->skills->isNotEmpty()));
    }

    public function taxonomy(string $taxonomy): JsonResponse
    {
        $modelClass = self::TAXONOMIES[$taxonomy];
        $query = $modelClass::query()->with('translations');

        if ($modelClass !== Tag::class) {
            $query->where('is_active', true)->orderBy('position');
        }

        return $this->list($query->orderBy('id')->get());
    }
}
