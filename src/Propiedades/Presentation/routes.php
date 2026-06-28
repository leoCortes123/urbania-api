<?php

declare(strict_types=1);

use Illuminate\Support\Facades\Route;
use Urbania\Propiedades\Infrastructure\Http\Controllers\PropertyStatusController;
use Urbania\Propiedades\Infrastructure\Http\Controllers\PropertyTypeController;

Route::middleware(['api', 'urbania.jwt', 'role:admin'])
    ->prefix('api/v1')
    ->group(function (): void {
        Route::get('/property-types', [PropertyTypeController::class, 'index']);
        Route::post('/property-types', [PropertyTypeController::class, 'store']);
        Route::patch('/property-types/{id}', [PropertyTypeController::class, 'update']);
        Route::delete('/property-types/{id}', [PropertyTypeController::class, 'destroy']);

        Route::get('/property-statuses', [PropertyStatusController::class, 'index']);
        Route::post('/property-statuses', [PropertyStatusController::class, 'store']);
        Route::patch('/property-statuses/{id}', [PropertyStatusController::class, 'update']);
        Route::delete('/property-statuses/{id}', [PropertyStatusController::class, 'destroy']);
    });
