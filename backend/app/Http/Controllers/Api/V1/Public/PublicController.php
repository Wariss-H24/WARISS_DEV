<?php

namespace App\Http\Controllers\Api\V1\Public;

use App\Http\Controllers\Controller;
use App\Http\Resources\Public\PublicPresenter;
use App\Models\Seo\SeoMetadata;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

/**
 * Base des contrôleurs de l'API publique : contenus publiés et visibles uniquement, dans la
 * langue de la requête (?locale=), représentés par PublicPresenter.
 */
abstract class PublicController extends Controller
{
    private const MAX_PER_PAGE = 50;

    public function __construct(protected readonly PublicPresenter $presenter) {}

    /**
     * @param  Builder<covariant Model>  $query
     */
    protected function paginated(Builder $query, Request $request, int $defaultPerPage = 12): JsonResponse
    {
        $perPage = min(max($request->integer('per_page', $defaultPerPage), 1), self::MAX_PER_PAGE);
        /** @var LengthAwarePaginator<int, Model> $page */
        $page = $query->paginate($perPage)->withQueryString();

        return response()->json([
            'data' => $this->presenter->many($page->items()),
            'meta' => [
                'current_page' => $page->currentPage(),
                'last_page' => $page->lastPage(),
                'per_page' => $page->perPage(),
                'total' => $page->total(),
            ],
        ]);
    }

    /**
     * @param  iterable<Model>  $models
     */
    protected function list(iterable $models): JsonResponse
    {
        return response()->json(['data' => $this->presenter->many($models)]);
    }

    /**
     * @param  array<string, mixed>  $extra
     */
    protected function item(Model $model, array $extra = []): JsonResponse
    {
        return response()->json(['data' => [...$this->presenter->one($model), ...$extra]]);
    }

    /**
     * Fiche SEO publique d'un contenu (null si aucune n'a été saisie).
     *
     * @return array<string, mixed>|null
     */
    protected function seo(Model $content): ?array
    {
        $seo = SeoMetadata::query()
            ->with('translations')
            ->where('seoable_type', $content->getMorphClass())
            ->where('seoable_id', $content->getKey())
            ->first();

        return $seo === null ? null : $this->presenter->one($seo);
    }
}
