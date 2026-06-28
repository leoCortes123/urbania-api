<?php

declare(strict_types=1);

namespace Urbania\Propiedades\Presentation;

use Illuminate\Support\ServiceProvider;
use Urbania\Propiedades\Domain\Repositories\PropertyStatusRepositoryInterface;
use Urbania\Propiedades\Domain\Repositories\PropertyTypeRepositoryInterface;
use Urbania\Propiedades\Infrastructure\Persistence\EloquentPropertyStatusRepository;
use Urbania\Propiedades\Infrastructure\Persistence\EloquentPropertyTypeRepository;

final class UrbaniaPropiedadesServiceProvider extends ServiceProvider
{
    public function register(): void
    {
        $this->app->bind(
            PropertyTypeRepositoryInterface::class,
            EloquentPropertyTypeRepository::class,
        );

        $this->app->bind(
            PropertyStatusRepositoryInterface::class,
            EloquentPropertyStatusRepository::class,
        );
    }

    public function boot(): void
    {
        $this->loadRoutesFrom(__DIR__.'/routes.php');
    }
}
