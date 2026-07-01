<?php

declare(strict_types=1);

use Illuminate\Support\Facades\Route;
use Urbania\Authorization\Infrastructure\Http\Controllers\ApprovalRuleController;
use Urbania\Authorization\Infrastructure\Http\Controllers\AssignmentController;
use Urbania\Authorization\Infrastructure\Http\Controllers\AuditController;
use Urbania\Authorization\Infrastructure\Http\Controllers\PermissionController;
use Urbania\Authorization\Infrastructure\Http\Controllers\RoleController;
use Urbania\Shared\Infrastructure\Middleware\AuthorizationMiddleware;

Route::middleware(['api', 'urbania.jwt', AuthorizationMiddleware::class])
    ->prefix('api/v1/authorization')
    ->group(function (): void {
        Route::get('/roles', [RoleController::class, 'index'])->name('roles.index');
        Route::post('/roles', [RoleController::class, 'store'])->name('roles.store');
        Route::patch('/roles/{id}', [RoleController::class, 'update'])->name('roles.update');
        Route::put('/roles/{id}/permissions', [RoleController::class, 'setPermissions'])->name('roles.setPermissions');

        Route::get('/permissions', [PermissionController::class, 'index'])->name('permissions.index');

        Route::post('/assignments', [AssignmentController::class, 'store'])->name('assignments.store');
        Route::delete('/assignments/{id}', [AssignmentController::class, 'destroy'])->name('assignments.destroy');

        Route::post('/approval-rules', [ApprovalRuleController::class, 'store'])->name('approval-rules.store');

        Route::get('/audit', [AuditController::class, 'index'])->name('audit.index');
    });
