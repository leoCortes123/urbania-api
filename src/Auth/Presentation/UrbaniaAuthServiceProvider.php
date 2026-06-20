<?php

declare(strict_types=1);

namespace Urbania\Auth\Presentation;

use Illuminate\Support\ServiceProvider;
use Urbania\Auth\Application\Services\JwtServiceInterface;
use Urbania\Auth\Domain\Repositories\RefreshTokenRepositoryInterface;
use Urbania\Auth\Domain\Repositories\UserRepositoryInterface;
use Urbania\Auth\Infrastructure\Events\IlluminateEventBus;
use Urbania\Auth\Infrastructure\Persistence\EloquentRefreshTokenRepository;
use Urbania\Auth\Infrastructure\Persistence\EloquentUserRepository;
use Urbania\Auth\Infrastructure\Services\PhpOpenSourceSaverJwtService;
use Urbania\Shared\Application\Bus\EventBusInterface;

final class UrbaniaAuthServiceProvider extends ServiceProvider
{
    public function register(): void
    {
        $this->app->bind(
            JwtServiceInterface::class,
            PhpOpenSourceSaverJwtService::class,
        );

        $this->app->bind(
            UserRepositoryInterface::class,
            EloquentUserRepository::class,
        );

        $this->app->bind(
            RefreshTokenRepositoryInterface::class,
            EloquentRefreshTokenRepository::class,
        );

        $this->app->bind(
            EventBusInterface::class,
            IlluminateEventBus::class,
        );
    }

    public function boot(): void
    {
        $this->loadRoutesFrom(__DIR__.'/routes.php');
    }
}
