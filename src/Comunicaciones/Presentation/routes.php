<?php

declare(strict_types=1);

use Illuminate\Support\Facades\Route;
use Urbania\Comunicaciones\Infrastructure\Http\Controllers\AnnouncementController;
use Urbania\Comunicaciones\Infrastructure\Http\Controllers\ChannelController;
use Urbania\Comunicaciones\Infrastructure\Http\Controllers\SurveyController;
use Urbania\Comunicaciones\Infrastructure\Http\Controllers\TemplateController;
use Urbania\Comunicaciones\Infrastructure\Http\Controllers\WebhookController;

Route::middleware(['api', 'urbania.jwt', 'role:admin'])
    ->prefix('api/v1/comunicaciones')
    ->group(function (): void {
        Route::get('/announcements', [AnnouncementController::class, 'index'])->name('comunicaciones.announcements.index');
        Route::post('/announcements', [AnnouncementController::class, 'store'])->name('comunicaciones.announcements.store');
        Route::get('/announcements/{id}', [AnnouncementController::class, 'show'])->name('comunicaciones.announcements.show');
        Route::delete('/announcements/{id}', [AnnouncementController::class, 'destroy'])->name('comunicaciones.announcements.destroy');

        Route::get('/templates', [TemplateController::class, 'index'])->name('comunicaciones.templates.index');
        Route::post('/templates', [TemplateController::class, 'store'])->name('comunicaciones.templates.store');
        Route::patch('/templates/{id}', [TemplateController::class, 'update'])->name('comunicaciones.templates.update');
        Route::delete('/templates/{id}', [TemplateController::class, 'destroy'])->name('comunicaciones.templates.destroy');

        Route::get('/surveys', [SurveyController::class, 'index'])->name('comunicaciones.surveys.index');
        Route::post('/surveys', [SurveyController::class, 'store'])->name('comunicaciones.surveys.store');
        Route::get('/surveys/{id}/results', [SurveyController::class, 'results'])->name('comunicaciones.surveys.results');

        Route::get('/channels', [ChannelController::class, 'index'])->name('comunicaciones.channels.index');
        Route::put('/channels', [ChannelController::class, 'update'])->name('comunicaciones.channels.update');
    });

Route::middleware(['api', 'urbania.jwt'])
    ->prefix('api/v1/comunicaciones')
    ->group(function (): void {
        Route::post('/surveys/{id}/responses', [SurveyController::class, 'respond'])->name('comunicaciones.surveys.respond');
    });

Route::middleware(['api', 'throttle:webhooks'])
    ->prefix('api/v1/comunicaciones')
    ->group(function (): void {
        Route::post('/webhooks/{provider}', [WebhookController::class, 'process'])->name('comunicaciones.webhooks.process');
    });
