<?php

namespace App\Http\Controllers\Api\V1\Admin\System;

use App\Enums\ActivityEvent;
use App\Http\Controllers\Api\V1\Public\PreviewController;
use App\Http\Controllers\Controller;
use App\Services\ActivityLogger;
use App\Services\PreviewToken;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\Relation;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;

/**
 * Génère le lien d'aperçu d'un contenu non publié (D-09), valable 30 minutes.
 *
 * Requête GET : générer un aperçu est une lecture, permise au compte viewer (le middleware
 * EnsureCanWrite n'autorise que les lectures à ce compte). Chaque génération est journalisée.
 */
class PreviewLinkController extends Controller
{
    public function __invoke(Request $request, PreviewToken $previewToken, ActivityLogger $activityLogger): JsonResponse
    {
        $validated = $request->validate([
            'type' => ['required', 'string', Rule::in(array_keys(PreviewController::TYPES))],
            'id' => ['required', 'integer', 'min:1'],
            'locale' => ['required', 'string', Rule::in((array) config('translatable.locales'))],
        ]);

        /** @var class-string<Model> $modelClass */
        $modelClass = Relation::getMorphedModel($validated['type']);
        $content = $modelClass::query()->findOrFail($validated['id']);

        $token = $previewToken->issue($validated['type'], (int) $validated['id'], $validated['locale']);
        $activityLogger->log(ActivityEvent::Updated, $content, description: 'aperçu généré');

        return response()->json(['data' => [
            'token' => $token,
            'url' => config('wariss.site_url')."/{$validated['locale']}/preview/{$token}",
            'expires_at' => now()->addMinutes(PreviewToken::LIFETIME_MINUTES)->toIso8601String(),
        ]]);
    }
}
