<?php

declare(strict_types=1);

namespace Urbania\Comunicaciones\Presentation;

use Illuminate\Support\ServiceProvider;
use Urbania\Comunicaciones\Domain\Repositories\AnnouncementDeliveryRepositoryInterface;
use Urbania\Comunicaciones\Domain\Repositories\AnnouncementRepositoryInterface;
use Urbania\Comunicaciones\Domain\Repositories\CommunicationChannelRepositoryInterface;
use Urbania\Comunicaciones\Domain\Repositories\MessageTemplateRepositoryInterface;
use Urbania\Comunicaciones\Domain\Repositories\SurveyRepositoryInterface;
use Urbania\Comunicaciones\Infrastructure\Persistence\EloquentAnnouncementDeliveryRepository;
use Urbania\Comunicaciones\Infrastructure\Persistence\EloquentAnnouncementRepository;
use Urbania\Comunicaciones\Infrastructure\Persistence\EloquentCommunicationChannelRepository;
use Urbania\Comunicaciones\Infrastructure\Persistence\EloquentMessageTemplateRepository;
use Urbania\Comunicaciones\Infrastructure\Persistence\EloquentSurveyRepository;

final class ComunicacionesServiceProvider extends ServiceProvider
{
    public function register(): void
    {
        $this->app->bind(
            AnnouncementRepositoryInterface::class,
            EloquentAnnouncementRepository::class,
        );

        $this->app->bind(
            AnnouncementDeliveryRepositoryInterface::class,
            EloquentAnnouncementDeliveryRepository::class,
        );

        $this->app->bind(
            CommunicationChannelRepositoryInterface::class,
            EloquentCommunicationChannelRepository::class,
        );

        $this->app->bind(
            MessageTemplateRepositoryInterface::class,
            EloquentMessageTemplateRepository::class,
        );

        $this->app->bind(
            SurveyRepositoryInterface::class,
            EloquentSurveyRepository::class,
        );
    }

    public function boot(): void
    {
        $this->loadRoutesFrom(__DIR__.'/routes.php');
    }
}
