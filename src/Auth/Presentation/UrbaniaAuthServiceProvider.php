<?php

declare(strict_types=1);

namespace Urbania\Auth\Presentation;

use Illuminate\Support\ServiceProvider;
use Urbania\Auth\Application\Services\JwtServiceInterface;
use Urbania\Auth\Infrastructure\Services\PhpOpenSourceSaverJwtService;

final class UrbaniaAuthServiceProvider extends ServiceProvider
{
    public function register(): void
    {
        $this->app->bind(
            JwtServiceInterface::class,
            PhpOpenSourceSaverJwtService::class,
        );
    }

    public function boot(): void
    {
        $this->loadRoutesFrom(__DIR__.'/routes.php');
    }
}
