<?php

namespace App\Http\Middleware;

use App\Models\User;
use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

/**
 * Le compte « viewer » est en lecture seule (décision D-04) : seules les requêtes de lecture
 * (GET, HEAD, OPTIONS) lui sont permises sur l'API d'administration. Contrôle côté serveur :
 * masquer les boutons dans l'interface n'est qu'un confort.
 */
class EnsureCanWrite
{
    /**
     * Handle an incoming request.
     *
     * @param  Closure(Request): (Response)  $next
     */
    public function handle(Request $request, Closure $next): Response
    {
        $user = $request->user();

        if ($request->isMethodSafe() || ($user instanceof User && $user->isAdmin())) {
            return $next($request);
        }

        abort(Response::HTTP_FORBIDDEN, __('Ce compte est en lecture seule.'));
    }
}
