<?php

use App\Http\Middleware\EnsureCanWrite;
use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| API v1 (préfixe /api/v1, défini dans bootstrap/app.php)
|--------------------------------------------------------------------------
|
| auth   : connexion, déconnexion, compte connecté.
| public : lecture des contenus publiés, formulaires et mesure d'audience.
| admin  : gestion des contenus ; le compte viewer est en lecture seule.
|
*/

Route::prefix('auth')->name('auth.')->group(base_path('routes/api/v1/auth.php'));

Route::prefix('public')->name('public.')
    ->middleware('throttle:public-read')
    ->group(base_path('routes/api/v1/public.php'));

Route::prefix('admin')->name('admin.')
    ->middleware(['auth:sanctum', EnsureCanWrite::class])
    ->group(base_path('routes/api/v1/admin.php'));
