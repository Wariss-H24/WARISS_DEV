<?php

use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Route;

Route::get('/', fn (): JsonResponse => response()->json([
    'name' => config('app.name'),
    'api' => url('/api/v1'),
]))->name('home');
