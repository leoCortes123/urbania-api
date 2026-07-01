<?php

declare(strict_types=1);

namespace Directorio\Presentation;

use Directorio\Application\Services\PropertyExistsCheckerInterface;
use Directorio\Domain\Repositories\ContactRepository;
use Directorio\Domain\Repositories\OccupantTypeRepository;
use Directorio\Domain\Repositories\PropertyOccupantRepository;
use Directorio\Infrastructure\Persistence\ContactRepositoryImpl;
use Directorio\Infrastructure\Persistence\OccupantTypeRepositoryImpl;
use Directorio\Infrastructure\Persistence\PropertyOccupantRepositoryImpl;
use Directorio\Infrastructure\Services\EloquentPropertyExistsChecker;
use Illuminate\Support\ServiceProvider;

class DirectorioServiceProvider extends ServiceProvider
{
    public function register(): void
    {
        $this->app->bind(ContactRepository::class, ContactRepositoryImpl::class);
        $this->app->bind(OccupantTypeRepository::class, OccupantTypeRepositoryImpl::class);
        $this->app->bind(PropertyOccupantRepository::class, PropertyOccupantRepositoryImpl::class);
        $this->app->bind(PropertyExistsCheckerInterface::class, EloquentPropertyExistsChecker::class);
    }

    public function boot(): void
    {
        $this->loadRoutesFrom(__DIR__.'/routes.php');
    }
}
