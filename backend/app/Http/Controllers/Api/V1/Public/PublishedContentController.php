<?php

namespace App\Http\Controllers\Api\V1\Public;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

/**
 * Contenus éditoriaux publiés (projets, études de cas, services, laboratoire, articles) :
 * liste filtrable et détail par slug (dans n'importe quelle langue), avec fiche SEO et galerie.
 * Un contenu non publié ou programmé pour plus tard répond 404, comme un contenu inexistant.
 */
abstract class PublishedContentController extends PublicController
{
    /**
     * @return class-string<Model>
     */
    abstract protected function model(): string;

    /**
     * Relations chargées dans la liste (clé => contrainte éventuelle).
     *
     * @return array<int|string, mixed>
     */
    abstract protected function listRelations(): array;

    /**
     * Relations supplémentaires du détail.
     *
     * @return array<int|string, mixed>
     */
    protected function detailRelations(): array
    {
        return [];
    }

    /**
     * Filtres de la liste lus dans la requête.
     *
     * @param  Builder<Model>  $query
     */
    protected function applyFilters(Builder $query, Request $request): void
    {
        //
    }

    /**
     * @param  Builder<Model>  $query
     */
    protected function applyOrder(Builder $query): void
    {
        $query->orderByDesc('is_featured')->orderBy('position')->orderByDesc('published_at')->orderByDesc('id');
    }

    public function index(Request $request): JsonResponse
    {
        $query = $this->published()->with($this->listRelations());
        $this->applyFilters($query, $request);

        if ($request->boolean('featured')) {
            $query->where('is_featured', true);
        }

        $this->applyOrder($query);

        return $this->paginated($query, $request);
    }

    public function show(string $slug): JsonResponse
    {
        $content = $this->published()
            ->with($this->fullRelations())
            ->whereTranslation('slug', $slug)
            ->firstOrFail();

        return $this->item($content, ['seo' => $this->seo($content)]);
    }

    /**
     * Aperçu d'un contenu quel que soit son statut (lien signé vérifié par PreviewController).
     */
    public function preview(int $id): JsonResponse
    {
        $modelClass = $this->model();
        $content = $modelClass::query()->with(['translations', ...$this->fullRelations()])->findOrFail($id);

        return $this->item($content, ['seo' => $this->seo($content), 'is_preview' => true]);
    }

    /**
     * @return array<int|string, mixed>
     */
    private function fullRelations(): array
    {
        return [...$this->listRelations(), ...$this->detailRelations()];
    }

    /**
     * @return Builder<Model>
     */
    protected function published(): Builder
    {
        $modelClass = $this->model();

        return $modelClass::query()->with('translations')->scopes(['published']);
    }

    /**
     * Contrainte d'une relation : éléments visibles, avec leurs traductions.
     */
    protected static function visible(string $flag = 'is_visible'): callable
    {
        return fn ($query) => $query->where($query->getModel()->qualifyColumn($flag), true)->with('translations');
    }

    /**
     * Contrainte d'une relation vers un contenu éditorial : publié seulement.
     */
    protected static function publishedOnly(): callable
    {
        return fn ($query) => $query->published()->with('translations');
    }

    /**
     * Filtre « catégorie » par slug traduit.
     *
     * @param  Builder<Model>  $query
     */
    protected static function filterByCategory(Builder $query, Request $request): void
    {
        if ($request->filled('category')) {
            $query->whereHas('category', fn (Builder $category) => $category->whereTranslation('slug', $request->string('category')->toString()));
        }
    }

    /**
     * Filtre « technologie » par slug.
     *
     * @param  Builder<Model>  $query
     */
    protected static function filterByTechnology(Builder $query, Request $request): void
    {
        if ($request->filled('technology')) {
            $query->whereHas('technologies', fn (Builder $technology) => $technology->where('slug', $request->string('technology')->toString()));
        }
    }

    /**
     * Recherche plein texte (D-08) dans la langue de la requête.
     *
     * @param  Builder<Model>  $query
     */
    protected static function filterBySearch(Builder $query, Request $request): void
    {
        if ($request->filled('search')) {
            $query->scopes(['search' => [mb_substr($request->string('search')->toString(), 0, 100), app()->getLocale()]]);
        }
    }
}
