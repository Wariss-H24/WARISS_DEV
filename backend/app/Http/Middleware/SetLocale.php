<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

/**
 * Langue de la requête API : paramètre ?locale=, sinon en-tête Accept-Language,
 * sinon langue par défaut. Seules les langues de config('translatable.locales') sont acceptées.
 * La langue retenue est renvoyée dans l'en-tête Content-Language.
 */
class SetLocale
{
    /**
     * Handle an incoming request.
     *
     * @param  Closure(Request): (Response)  $next
     */
    public function handle(Request $request, Closure $next): Response
    {
        /** @var list<string> $supportedLocales */
        $supportedLocales = config('translatable.locales');

        $requested = $request->query('locale');
        $locale = is_string($requested) && in_array($requested, $supportedLocales, true)
            ? $requested
            : ($request->getPreferredLanguage($supportedLocales) ?? (string) config('app.locale'));

        app()->setLocale($locale);

        $response = $next($request);
        $response->headers->set('Content-Language', $locale);

        return $response;
    }
}
