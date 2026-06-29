<?php

declare(strict_types=1);

namespace Urbania\Auth\Presentation;

use Illuminate\Support\ServiceProvider;
use PragmaRX\Google2FA\Google2FA;
use Urbania\Auth\Application\Services\AvatarStorageServiceInterface;
use Urbania\Auth\Application\Services\JwtServiceInterface;
use Urbania\Auth\Application\Services\MailerServiceInterface;
use Urbania\Auth\Application\Services\PasswordHistoryServiceInterface;
use Urbania\Auth\Application\UseCases\ForgotPasswordUseCase;
use Urbania\Auth\Application\UseCases\ResendVerificationUseCase;
use Urbania\Auth\Domain\Repositories\PasswordResetTokenRepositoryInterface;
use Urbania\Auth\Domain\Repositories\RefreshTokenRepositoryInterface;
use Urbania\Auth\Domain\Repositories\UserRepositoryInterface;
use Urbania\Auth\Infrastructure\Events\IlluminateEventBus;
use Urbania\Auth\Infrastructure\Persistence\EloquentPasswordHistoryService;
use Urbania\Auth\Infrastructure\Persistence\EloquentPasswordResetTokenRepository;
use Urbania\Auth\Infrastructure\Persistence\EloquentRefreshTokenRepository;
use Urbania\Auth\Infrastructure\Persistence\EloquentUserRepository;
use Urbania\Auth\Infrastructure\Services\JwtTokenDecoder;
use Urbania\Auth\Infrastructure\Services\LaravelMailerService;
use Urbania\Auth\Infrastructure\Services\LocalAvatarStorageService;
use Urbania\Auth\Infrastructure\Services\PhpOpenSourceSaverJwtService;
use Urbania\Shared\Application\Bus\EventBusInterface;
use Urbania\Shared\Application\Services\TokenDecoderInterface;

final class UrbaniaAuthServiceProvider extends ServiceProvider
{
    public function register(): void
    {
        $this->app->singleton(Google2FA::class, function (): Google2FA {
            $google2fa = new Google2FA;
            $google2fa->setAlgorithm('sha256');

            return $google2fa;
        });

        $this->app->bind(
            JwtServiceInterface::class,
            PhpOpenSourceSaverJwtService::class,
        );

        $this->app->bind(
            TokenDecoderInterface::class,
            JwtTokenDecoder::class,
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

        $this->app->bind(
            PasswordHistoryServiceInterface::class,
            EloquentPasswordHistoryService::class,
        );

        $this->app->bind(
            MailerServiceInterface::class,
            LaravelMailerService::class,
        );

        $this->app->bind(
            PasswordResetTokenRepositoryInterface::class,
            EloquentPasswordResetTokenRepository::class,
        );

        $this->app->bind(
            AvatarStorageServiceInterface::class,
            LocalAvatarStorageService::class,
        );

        $frontendUrl = config('app.frontend_url', config('app.url', 'http://localhost'));
        assert(is_string($frontendUrl) && $frontendUrl !== '');

        $this->app->when([
            ForgotPasswordUseCase::class,
            ResendVerificationUseCase::class,
        ])->needs('$frontendUrl')->give($frontendUrl);
    }

    public function boot(): void
    {
        $this->loadRoutesFrom(__DIR__.'/routes.php');
    }
}
