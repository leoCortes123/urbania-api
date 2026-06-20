<?php

declare(strict_types=1);

use App\Http\Controllers\HealthController;
use Illuminate\Support\Facades\Route;

Route::get('/v1/health', [HealthController::class, 'check']);
