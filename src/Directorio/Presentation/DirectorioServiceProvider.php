<?php

declare(strict_types=1);

namespace Directorio\Presentation;

use Directorio\Domain\Repositories\ContactRepository;
use Directorio\Domain\Repositories\OccupantTypeRepository;
use Directorio\Domain\Repositories\PropertyOccupantRepository;
use Directorio\Infrastructure\Persistence\ContactRepositoryImpl;
use Directorio\Infrastructure\Persistence\OccupantTypeRepositoryImpl;
use Directorio\Infrastructure\Persistence\PropertyOccupantRepositoryImpl;
use Illuminate\Support\ServiceProvider;

class DirectorioServiceProvider extends ServiceProvider
{
    public function register(): void
    {
        $this->app->bind(ContactRepository::class, ContactRepositoryImpl::class);
        $this->app->bind(OccupantTypeRepository::class, OccupantTypeRepositoryImpl::class);
        $this->app->bind(PropertyOccupantRepository::class, PropertyOccupantRepositoryImpl::class);
    }

    public function boot(): void
    {
        $this->loadRoutesFrom(__DIR__.'/routes.php');
    }
}
