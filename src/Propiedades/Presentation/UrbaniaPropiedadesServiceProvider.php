<?php

declare(strict_types=1);

namespace Urbania\Propiedades\Presentation;

use Illuminate\Support\ServiceProvider;
use Urbania\Propiedades\Domain\Repositories\CondominiumRepositoryInterface;
use Urbania\Propiedades\Domain\Repositories\PropertyDocumentRepositoryInterface;
use Urbania\Propiedades\Domain\Repositories\PropertyDocumentTypeRepositoryInterface;
use Urbania\Propiedades\Domain\Repositories\PropertyRepositoryInterface;
use Urbania\Propiedades\Domain\Repositories\PropertyStatusLogRepositoryInterface;
use Urbania\Propiedades\Domain\Repositories\PropertyStatusRepositoryInterface;
use Urbania\Propiedades\Domain\Repositories\PropertyTypeRepositoryInterface;
use Urbania\Propiedades\Domain\Repositories\TowerRepositoryInterface;
use Urbania\Propiedades\Infrastructure\Persistence\EloquentCondominiumRepository;
use Urbania\Propiedades\Infrastructure\Persistence\EloquentPropertyDocumentRepository;
use Urbania\Propiedades\Infrastructure\Persistence\EloquentPropertyDocumentTypeRepository;
use Urbania\Propiedades\Infrastructure\Persistence\EloquentPropertyRepository;
use Urbania\Propiedades\Infrastructure\Persistence\EloquentPropertyStatusLogRepository;
use Urbania\Propiedades\Infrastructure\Persistence\EloquentPropertyStatusRepository;
use Urbania\Propiedades\Infrastructure\Persistence\EloquentPropertyTypeRepository;
use Urbania\Propiedades\Infrastructure\Persistence\EloquentTowerRepository;

final class UrbaniaPropiedadesServiceProvider extends ServiceProvider
{
    public function register(): void
    {
        $this->app->bind(
            CondominiumRepositoryInterface::class,
            EloquentCondominiumRepository::class,
        );

        $this->app->bind(
            TowerRepositoryInterface::class,
            EloquentTowerRepository::class,
        );

        $this->app->bind(
            PropertyTypeRepositoryInterface::class,
            EloquentPropertyTypeRepository::class,
        );

        $this->app->bind(
            PropertyStatusRepositoryInterface::class,
            EloquentPropertyStatusRepository::class,
        );

        $this->app->bind(
            PropertyRepositoryInterface::class,
            EloquentPropertyRepository::class,
        );

        $this->app->bind(
            PropertyStatusLogRepositoryInterface::class,
            EloquentPropertyStatusLogRepository::class,
        );

        $this->app->bind(
            PropertyDocumentRepositoryInterface::class,
            EloquentPropertyDocumentRepository::class,
        );

        $this->app->bind(
            PropertyDocumentTypeRepositoryInterface::class,
            EloquentPropertyDocumentTypeRepository::class,
        );
    }

    public function boot(): void
    {
        $this->loadRoutesFrom(__DIR__.'/routes.php');
    }
}
