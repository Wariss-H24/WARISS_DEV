<?php

namespace App\Http\Controllers\Api\V1\Public;

use App\Http\Controllers\Controller;
use App\Services\PreviewToken;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Response;

/**
 * Aperçu d'un brouillon par lien signé (D-09) : le contenu est renvoyé quel que soit son statut,
 * jamais mis en cache ni indexé, et sans mesure d'audience.
 */
class PreviewController extends Controller
{
    /**
     * Types prévisualisables : alias de la morph map => contrôleur public qui sait les présenter.
     *
     * @var array<string, class-string<PageController|PublishedContentController>>
     */
    public const TYPES = [
        'page' => PageController::class,
        'project' => ProjectController::class,
        'case_study' => CaseStudyController::class,
        'service' => ServiceController::class,
        'experiment' => ExperimentController::class,
        'post' => PostController::class,
    ];

    public function __invoke(string $token, PreviewToken $previewToken): JsonResponse
    {
        $target = $previewToken->verify($token);
        abort_if($target === null || ! isset(self::TYPES[$target['type']]), Response::HTTP_NOT_FOUND, __('Ce lien d\'aperçu est invalide ou a expiré.'));

        app()->setLocale($target['locale']);

        /** @var PageController|PublishedContentController $controller */
        $controller = app(self::TYPES[$target['type']]);
        $response = $controller->preview($target['id']);

        $response->headers->set('Cache-Control', 'no-store, private');
        $response->headers->set('X-Robots-Tag', 'noindex, nofollow');
        $response->headers->set('Content-Language', $target['locale']);

        return $response;
    }
}
