<?php

declare(strict_types=1);

use Directorio\Infrastructure\Http\Controllers\ContactController;
use Directorio\Infrastructure\Http\Controllers\OccupantTypeController;
use Directorio\Infrastructure\Http\Controllers\PropertyOccupantController;
use Illuminate\Support\Facades\Route;

Route::middleware(['api', 'urbania.jwt'])->prefix('api/v1')->group(function () {
    // Catálogos
    Route::get('occupant-types', [OccupantTypeController::class, 'index']);

    // Contactos (admin)
    Route::middleware(['role:admin'])->group(function () {
        Route::get('contacts', [ContactController::class, 'index']);
        Route::post('contacts', [ContactController::class, 'store']);
        Route::get('contacts/{contact}', [ContactController::class, 'show']);
        Route::patch('contacts/{contact}', [ContactController::class, 'update']);
        Route::delete('contacts/{contact}', [ContactController::class, 'destroy']);
    });

    // Propiedades de un contacto
    Route::get('contacts/{contact}/properties', [ContactController::class, 'properties']);

    // Ocupantes por unidad
    Route::get('properties/{propertyId}/occupants', [PropertyOccupantController::class, 'index']);

    // Administración de ocupantes (admin)
    Route::middleware(['role:admin'])->group(function () {
        Route::post('properties/{propertyId}/occupants', [PropertyOccupantController::class, 'store']);
        Route::patch('property-occupants/{occupant}', [PropertyOccupantController::class, 'update']);
        Route::delete('property-occupants/{occupant}', [PropertyOccupantController::class, 'destroy']);
    });
});
