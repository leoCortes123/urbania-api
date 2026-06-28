<?php

declare(strict_types=1);

use Illuminate\Support\Facades\Route;
use Urbania\Propiedades\Infrastructure\Http\Controllers\CondominiumController;
use Urbania\Propiedades\Infrastructure\Http\Controllers\PropertyController;
use Urbania\Propiedades\Infrastructure\Http\Controllers\PropertyDocumentController;
use Urbania\Propiedades\Infrastructure\Http\Controllers\PropertyDocumentTypeController;
use Urbania\Propiedades\Infrastructure\Http\Controllers\PropertyStatusController;
use Urbania\Propiedades\Infrastructure\Http\Controllers\PropertyTypeController;
use Urbania\Propiedades\Infrastructure\Http\Controllers\TowerController;

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

        Route::get('/condominiums', [CondominiumController::class, 'index']);
        Route::get('/condominiums/{id}', [CondominiumController::class, 'show']);
        Route::patch('/condominiums/{id}', [CondominiumController::class, 'update']);
        Route::get('/condominiums/{id}/coefficient-validation', [CondominiumController::class, 'coefficientValidation']);

        Route::get('/condominiums/{condominiumId}/towers', [TowerController::class, 'index']);
        Route::post('/towers', [TowerController::class, 'store']);
        Route::get('/towers/{id}', [TowerController::class, 'show']);
        Route::patch('/towers/{id}', [TowerController::class, 'update']);
        Route::delete('/towers/{id}', [TowerController::class, 'destroy']);

        Route::get('/properties', [PropertyController::class, 'index']);
        Route::post('/properties', [PropertyController::class, 'store']);
        Route::get('/properties/{id}', [PropertyController::class, 'show']);
        Route::patch('/properties/{id}', [PropertyController::class, 'update']);
        Route::delete('/properties/{id}', [PropertyController::class, 'destroy']);
        Route::patch('/properties/{id}/status', [PropertyController::class, 'changeStatus']);
        Route::get('/properties/{id}/status-log', [PropertyController::class, 'statusLog']);

        Route::get('/property-document-types', [PropertyDocumentTypeController::class, 'index']);
        Route::post('/property-document-types', [PropertyDocumentTypeController::class, 'store']);
        Route::patch('/property-document-types/{id}', [PropertyDocumentTypeController::class, 'update']);
        Route::delete('/property-document-types/{id}', [PropertyDocumentTypeController::class, 'destroy']);

        Route::get('/properties/{propertyId}/documents', [PropertyDocumentController::class, 'index']);
        Route::post('/properties/{propertyId}/documents', [PropertyDocumentController::class, 'store']);
        Route::delete('/properties/{propertyId}/documents/{docId}', [PropertyDocumentController::class, 'destroy']);
    });
