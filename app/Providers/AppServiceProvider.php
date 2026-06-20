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

            return Limit::perMinutes(15, 5)->by($key);
        });

        RateLimiter::for('register', function (Request $request): Limit {
            return Limit::perHour(3)->by($request->ip());
        });

        RateLimiter::for('refresh', function (Request $request): Limit {
            return Limit::perMinutes(15, 10)->by($request->ip());
        });

        RateLimiter::for('api', function (Request $request): Limit {
            $key = $request->user() !== null ? $request->user()->id : $request->ip();

            return Limit::perMinute(1000)->by((string) $key);
        });
    }
}
