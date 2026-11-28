<?php

namespace App\Http\Controllers\Api\V1\Public;

use App\Models\Blog\Post;
use App\Models\Lab\Experiment;
use App\Models\Portfolio\Project;
use App\Models\Services\Service;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

/**
 * Recherche plein texte (D-08) dans les contenus publiés, dans la langue de la requête.
 * Syntaxe « moteur de recherche » : mots, "phrase exacte", -exclusion, or.
 */
class SearchController extends PublicController
{
    /**
     * @var array<string, class-string<Project|Service|Experiment|Post>>
     */
    private const TYPES = [
        'projects' => Project::class,
        'services' => Service::class,
        'experiments' => Experiment::class,
        'posts' => Post::class,
    ];

    public function __invoke(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'q' => ['required', 'string', 'min:2', 'max:100'],
            'types' => ['sometimes', 'array'],
            'types.*' => ['string', 'in:'.implode(',', array_keys(self::TYPES))],
            'limit' => ['sometimes', 'integer', 'min:1', 'max:20'],
        ]);

        $types = $validated['types'] ?? array_keys(self::TYPES);
        $limit = (int) ($validated['limit'] ?? 5);
        $results = [];

        foreach ($types as $type) {
            $modelClass = self::TYPES[$type];

            $results[$type] = $this->presenter->many($modelClass::query()
                ->published()
                ->search($validated['q'], app()->getLocale())
                ->with('translations')
                ->orderByDesc('published_at')
                ->limit($limit)
                ->get());
        }

        return response()->json(['data' => $results]);
    }
}
