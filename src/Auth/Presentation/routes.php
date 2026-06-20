<?php

declare(strict_types=1);

use Illuminate\Support\Facades\Route;
use Urbania\Auth\Infrastructure\Http\Controllers\AuthController;

Route::middleware('api')->prefix('api/v1/auth')->group(function (): void {
    Route::post('/login', [AuthController::class, 'login'])->middleware('throttle:login');
    Route::post('/register', [AuthController::class, 'register'])->middleware('throttle:register');
    Route::post('/refresh', [AuthController::class, 'refresh'])->middleware('throttle:refresh');

    Route::middleware(['jwt.auth'])->group(function (): void {
        Route::post('/logout', [AuthController::class, 'logout'])->middleware('throttle:api');
        Route::get('/me', [AuthController::class, 'me'])->middleware('throttle:api');
    });
});
