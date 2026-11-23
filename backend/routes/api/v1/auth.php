<?php

use App\Http\Controllers\Api\V1\Auth\CurrentUserController;
use App\Http\Controllers\Api\V1\Auth\SessionController;
use Illuminate\Support\Facades\Route;

Route::post('session', [SessionController::class, 'store'])
    ->middleware('throttle:auth')
    ->name('session.store');

Route::middleware('auth:sanctum')->group(function (): void {
    Route::delete('session', [SessionController::class, 'destroy'])->name('session.destroy');
    Route::get('me', CurrentUserController::class)->name('me');
});
