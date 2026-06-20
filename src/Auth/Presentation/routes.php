<?php

declare(strict_types=1);

use Illuminate\Support\Facades\Route;
use Urbania\Auth\Infrastructure\Http\Controllers\AuthController;

Route::middleware('api')->prefix('api/v1/auth')->group(function (): void {
    Route::post('/login', [AuthController::class, 'login'])->middleware('throttle:login');
    Route::post('/register', [AuthController::class, 'register'])->middleware('throttle:register');
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
