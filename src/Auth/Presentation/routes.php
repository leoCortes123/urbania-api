<?php

declare(strict_types=1);

use Illuminate\Support\Facades\Route;
use Urbania\Auth\Infrastructure\Http\Controllers\AdminResidentController;
use Urbania\Auth\Infrastructure\Http\Controllers\AuthController;
use Urbania\Auth\Infrastructure\Http\Controllers\ImpersonationController;
use Urbania\Auth\Infrastructure\Http\Controllers\MyUnitController;

Route::middleware('api')->prefix('api/v1/auth')->group(function (): void {
    Route::post('/login', [AuthController::class, 'login'])->middleware('throttle:login');
    Route::post('/register', [AuthController::class, 'register'])->middleware('throttle:register');
    Route::post('/activate', [AuthController::class, 'activate'])->middleware('throttle:api');
    Route::post('/register-by-invitation', [MyUnitController::class, 'registerByInvitation'])->middleware('throttle:register');
    Route::post('/refresh', [AuthController::class, 'refresh'])->middleware('throttle:refresh');

    Route::post('/forgot-password', [AuthController::class, 'forgotPassword'])->middleware('throttle:forgot-password');
    Route::post('/reset-password', [AuthController::class, 'resetPassword']);
    Route::post('/verify-email', [AuthController::class, 'verifyEmail']);

    Route::middleware(['urbania.jwt'])->group(function (): void {
        Route::post('/logout', [AuthController::class, 'logout'])->middleware('throttle:api');
        Route::get('/me', [AuthController::class, 'me'])->middleware('throttle:api');
        Route::patch('/me', [AuthController::class, 'updateProfile'])->middleware('throttle:api');
        Route::post('/change-password', [AuthController::class, 'changePassword'])->middleware('throttle:api');
        Route::post('/resend-verification', [AuthController::class, 'resendVerification'])->middleware('throttle:verification-resend');

        Route::post('/mfa/setup', [AuthController::class, 'mfaSetup'])->middleware('throttle:api');
        Route::post('/mfa/enable', [AuthController::class, 'mfaEnable'])->middleware('throttle:api');
        Route::post('/mfa/disable', [AuthController::class, 'mfaDisable'])->middleware('throttle:api');
        Route::post('/mfa/backup-codes', [AuthController::class, 'mfaRegenerateBackupCodes'])->middleware('throttle:api');

        Route::get('/sessions', [AuthController::class, 'listSessions'])->middleware('throttle:api');
        Route::delete('/sessions', [AuthController::class, 'revokeAllSessions'])->middleware('throttle:api');
        Route::delete('/sessions/{sessionId}', [AuthController::class, 'revokeSession'])->middleware('throttle:api');
    });

    Route::post('/mfa/verify', [AuthController::class, 'mfaVerify'])->middleware('throttle:mfa-verify');
    Route::post('/mfa/verify-backup', [AuthController::class, 'mfaVerifyBackup'])->middleware('throttle:mfa-verify');
});

// Public invitation verification
Route::middleware('api')->prefix('api/v1/invitations')->group(function (): void {
    Route::get('/{token}', [MyUnitController::class, 'verifyInvitation'])->middleware('throttle:api');
});

// My Unit routes (portal primary)
Route::middleware(['api', 'urbania.jwt'])->prefix('api/v1/my-unit')->group(function (): void {
    Route::get('/occupants', [MyUnitController::class, 'listOccupants'])->middleware('throttle:api');
    Route::get('/invitations', [MyUnitController::class, 'listInvitations'])->middleware('throttle:api');
    Route::post('/invitations', [MyUnitController::class, 'createInvitation'])->middleware('throttle:api');
    Route::delete('/invitations/{id}', [MyUnitController::class, 'cancelInvitation'])->middleware('throttle:api');
});

// Admin resident management routes
Route::middleware(['api', 'urbania.jwt', 'role:admin'])->prefix('api/v1/admin/residents')->group(function (): void {
    Route::post('/{occupantId}/enable-portal', [AdminResidentController::class, 'enablePortal'])->middleware('throttle:api');
    Route::post('/{occupantId}/resend-activation', [AdminResidentController::class, 'resendActivation'])->middleware('throttle:api');
});

// Admin impersonation routes
Route::middleware(['api', 'urbania.jwt', 'role:admin'])->prefix('api/v1/admin')->group(function (): void {
    Route::post('/impersonate', [ImpersonationController::class, 'impersonate'])->middleware('throttle:api');
});

// Impersonation stop — requires any valid impersonation token (imp claim present)
Route::middleware(['api', 'urbania.jwt'])->prefix('api/v1/admin')->group(function (): void {
    Route::post('/impersonate/stop', [ImpersonationController::class, 'stop'])->middleware('throttle:api');
});
