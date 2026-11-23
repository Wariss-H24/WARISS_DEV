<?php

namespace App\Http\Controllers\Api\V1\Auth;

use App\Enums\ActivityEvent;
use App\Http\Controllers\Controller;
use App\Http\Requests\Auth\LoginRequest;
use App\Http\Resources\Admin\Identity\CurrentUserResource;
use App\Models\User;
use App\Services\ActivityLogger;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\Auth;

/**
 * Session d'administration (Sanctum SPA, cookie de session) : connexion et déconnexion.
 */
class SessionController extends Controller
{
    public function __construct(private readonly ActivityLogger $activityLogger) {}

    /**
     * Ouvre une session. Le frontend appelle d'abord GET /sanctum/csrf-cookie.
     */
    public function store(LoginRequest $request): CurrentUserResource
    {
        $user = $request->authenticate();

        $request->session()->regenerate();

        $user->forceFill(['last_login_at' => now()])->save();
        $this->activityLogger->log(ActivityEvent::Login, $user, user: $user);

        return new CurrentUserResource($user);
    }

    /**
     * Ferme la session et invalide le jeton CSRF.
     */
    public function destroy(Request $request): Response
    {
        /** @var User $user */
        $user = $request->user();
        $this->activityLogger->log(ActivityEvent::Logout, $user, user: $user);

        Auth::guard('web')->logout();
        $request->session()->invalidate();
        $request->session()->regenerateToken();

        return response()->noContent();
    }
}
