<?php

declare(strict_types=1);

namespace App\Providers;

use Illuminate\Cache\RateLimiting\Limit;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\RateLimiter;
use Illuminate\Support\ServiceProvider;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        //
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        RateLimiter::for('login', function (Request $request): Limit {
            $key = $request->string('email')->value().'|'.$request->ip();
            $maxAttempts = (int) env('RATE_LIMIT_LOGIN_MAX_ATTEMPTS', 5);
            $decayMinutes = (int) env('RATE_LIMIT_LOGIN_DECAY_MINUTES', 15);

            return Limit::perMinutes($decayMinutes, $maxAttempts)->by($key);
        });

        RateLimiter::for('register', function (Request $request): Limit {
            return Limit::perHour(3)->by($request->ip());
        });

        RateLimiter::for('refresh', function (Request $request): Limit {
            $maxAttempts = (int) env('RATE_LIMIT_REFRESH_MAX_ATTEMPTS', 10);
            $decayMinutes = (int) env('RATE_LIMIT_REFRESH_DECAY_MINUTES', 15);

            return Limit::perMinutes($decayMinutes, $maxAttempts)->by($request->ip());
        });

        RateLimiter::for('api', function (Request $request): Limit {
            $key = $request->user() !== null ? $request->user()->id : $request->ip();
            $maxAttempts = (int) env('RATE_LIMIT_API_MAX_ATTEMPTS', 1000);
            $decayMinutes = (int) env('RATE_LIMIT_API_DECAY_MINUTES', 1);

            return Limit::perMinutes($decayMinutes, $maxAttempts)->by((string) $key);
        });

        RateLimiter::for('mfa-verify', function (Request $request): Limit {
            return Limit::perMinutes(5, 3)->by($request->ip());
        });

        RateLimiter::for('forgot-password', function (Request $request): Limit {
            $key = $request->string('email')->value().'|'.$request->ip();

            return Limit::perHour(3)->by($key);
        });

        RateLimiter::for('verification-resend', function (Request $request): Limit {
            $userId = $request->attributes->get('auth_user_id');
            $key = is_string($userId) && $userId !== '' ? $userId : (string) $request->ip();

            return Limit::perHour(3)->by($key);
        });
    }
}
