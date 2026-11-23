<?php

namespace App\Http\Controllers\Api\V1\Auth;

use App\Http\Controllers\Controller;
use App\Http\Resources\Admin\Identity\CurrentUserResource;
use Illuminate\Http\Request;

/**
 * Compte actuellement connecté.
 */
class CurrentUserController extends Controller
{
    public function __invoke(Request $request): CurrentUserResource
    {
        return new CurrentUserResource($request->user());
    }
}
