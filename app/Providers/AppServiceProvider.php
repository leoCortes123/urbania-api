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
            /** @var int $maxAttempts */
            $maxAttempts = config('rate-limiting.login.max_attempts', 5);
            /** @var int $decayMinutes */
            $decayMinutes = config('rate-limiting.login.decay_minutes', 15);

            return Limit::perMinutes($decayMinutes, $maxAttempts)->by($key);
        });

        RateLimiter::for('register', function (Request $request): Limit {
            return Limit::perHour(3)->by($request->ip());
        });

        RateLimiter::for('refresh', function (Request $request): Limit {
            /** @var int $maxAttempts */
            $maxAttempts = config('rate-limiting.refresh.max_attempts', 10);
            /** @var int $decayMinutes */
            $decayMinutes = config('rate-limiting.refresh.decay_minutes', 15);

            return Limit::perMinutes($decayMinutes, $maxAttempts)->by($request->ip());
        });

        RateLimiter::for('api', function (Request $request): Limit {
            $key = $request->user() !== null ? $request->user()->id : $request->ip();
            /** @var int $maxAttempts */
            $maxAttempts = config('rate-limiting.api.max_attempts', 1000);
            /** @var int $decayMinutes */
            $decayMinutes = config('rate-limiting.api.decay_minutes', 1);

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
