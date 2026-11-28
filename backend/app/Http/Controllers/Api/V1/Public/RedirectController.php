<?php

namespace App\Http\Controllers\Api\V1\Public;

use App\Http\Controllers\Controller;
use App\Models\Seo\Redirect;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Response;

/**
 * Résolution des redirections (D-06, D16) : le site public demande, pour une adresse introuvable,
 * si une redirection active existe ; chaque redirection suivie est comptée (hits).
 */
class RedirectController extends Controller
{
    public function __invoke(Request $request): JsonResponse
    {
        $validated = $request->validate(['path' => ['required', 'string', 'max:2048', 'regex:#^/(?!/)#']]);
        $path = rtrim((string) strtok($validated['path'], '?#'), '/') ?: '/';

        $redirect = Redirect::query()
            ->where('is_active', true)
            ->whereIn('from_path', array_unique([$path, $path.'/']))
            ->first();

        abort_if($redirect === null, Response::HTTP_NOT_FOUND);

        $redirect->increment('hits');

        return response()->json(['data' => [
            'to_path' => $redirect->to_path,
            'status_code' => $redirect->status_code,
        ]]);
    }
}
