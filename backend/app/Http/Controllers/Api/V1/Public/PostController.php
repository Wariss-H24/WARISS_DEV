<?php

namespace App\Http\Controllers\Api\V1\Public;

use App\Models\Blog\Post;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Http\Request;

/**
 * Articles publiés (D12), du plus récent au plus ancien. Le contenu est renvoyé en Markdown :
 * le site public le convertit et le nettoie à l'affichage.
 * Filtres : category, tag, type, search, featured.
 */
class PostController extends PublishedContentController
{
    protected function model(): string
    {
        return Post::class;
    }

    protected function listRelations(): array
    {
        return [
            'category.translations',
            'tags.translations',
        ];
    }

    protected function detailRelations(): array
    {
        return ['gallery.translations'];
    }

    protected function applyFilters(Builder $query, Request $request): void
    {
        self::filterByCategory($query, $request);
        self::filterBySearch($query, $request);

        if ($request->filled('tag')) {
            $query->whereHas('tags', fn (Builder $tag) => $tag->whereTranslation('slug', $request->string('tag')->toString()));
        }

        if ($request->filled('type')) {
            $query->where('type', $request->string('type')->toString());
        }
    }

    protected function applyOrder(Builder $query): void
    {
        $query->orderByDesc('published_at')->orderByDesc('id');
    }
}
