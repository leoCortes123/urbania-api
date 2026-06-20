<?php

declare(strict_types=1);

namespace Urbania\Auth\Application\UseCases;

use PragmaRX\Google2FA\Google2FA;
use Urbania\Auth\Domain\Events\MfaEnabled;
use Urbania\Auth\Domain\Exceptions\MfaInvalidCodeException;
use Urbania\Auth\Domain\Exceptions\MfaNotConfiguredException;
use Urbania\Auth\Domain\Exceptions\UserNotFoundException;
use Urbania\Auth\Domain\Repositories\UserRepositoryInterface;
use Urbania\Shared\Application\Bus\EventBusInterface;
use Urbania\Shared\Domain\ValueObjects\Uuid;

final readonly class MfaEnableUseCase
{
    public function __construct(
        private UserRepositoryInterface $userRepository,
        private EventBusInterface $eventBus,
    ) {}

    public function execute(string $userId, string $code, string $ipAddress): void
    {
        $user = $this->userRepository->findById(Uuid::fromString($userId));

        if ($user === null || $user->deletedAt() !== null) {
            throw new UserNotFoundException;
        }

        if ($user->isMfaEnabled()) {
            return;
        }

        if ($user->mfaSecret() === null) {
            throw new MfaNotConfiguredException;
        }

        $google2fa = new Google2FA;
        $google2fa->setAlgorithm('sha256');

        if (! $google2fa->verifyKey($user->mfaSecret(), $code, 2)) {
            throw new MfaInvalidCodeException;
        }

        $user->enableMfa($user->mfaSecret(), $user->mfaBackupCodes());
        $this->userRepository->update($user);

        $this->eventBus->dispatch(new MfaEnabled(
            userId: $user->id()->toString(),
            ip: $ipAddress,
            timestamp: new \DateTimeImmutable,
        ));
    }
}
