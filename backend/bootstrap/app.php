<?php

use App\Http\Middleware\SetLocale;
use Illuminate\Database\QueryException;
use Illuminate\Foundation\Application;
use Illuminate\Foundation\Configuration\Exceptions;
use Illuminate\Foundation\Configuration\Middleware;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

return Application::configure(basePath: dirname(__DIR__))
    ->withRouting(
        web: __DIR__.'/../routes/web.php',
        api: __DIR__.'/../routes/api.php',
        apiPrefix: 'api/v1',
        commands: __DIR__.'/../routes/console.php',
        health: '/up',
    )
    ->withMiddleware(function (Middleware $middleware): void {
        // Authentification de l'administration par cookie de session Sanctum (SPA), CSRF compris.
        $middleware->statefulApi();
        $middleware->api(append: [SetLocale::class]);
    })
    ->withExceptions(function (Exceptions $exceptions): void {
        $exceptions->shouldRenderJsonWhen(
            fn (Request $request) => $request->is('api/*') || $request->expectsJson(),
        );

        // Filet de sécurité : une règle de la base qui refuse une écriture (CHECK, unicité) est une
        // erreur du client, pas du serveur. La validation la signale normalement avant.
        $exceptions->render(function (QueryException $exception, Request $request): ?JsonResponse {
            if (! $request->is('api/*')) {
                return null;
            }

            return match ($exception->getCode()) {
                '23514', '23502' => response()->json(['message' => __('Les données envoyées ne respectent pas les règles de ce contenu.')], 422),
                '23505' => response()->json(['message' => __('Un contenu identique existe déjà.')], 409),
                default => null,
            };
        });
    })->create();
