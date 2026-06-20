<?php

declare(strict_types=1);

namespace Urbania\Auth\Application\UseCases;

use PragmaRX\Google2FA\Google2FA;
use Urbania\Auth\Domain\Events\MfaDisabled;
use Urbania\Auth\Domain\Exceptions\InvalidCredentialsException;
use Urbania\Auth\Domain\Exceptions\MfaInvalidCodeException;
use Urbania\Auth\Domain\Exceptions\MfaNotEnabledException;
use Urbania\Auth\Domain\Exceptions\UserNotFoundException;
use Urbania\Auth\Domain\Repositories\RefreshTokenRepositoryInterface;
use Urbania\Auth\Domain\Repositories\UserRepositoryInterface;
use Urbania\Shared\Application\Bus\EventBusInterface;
use Urbania\Shared\Domain\ValueObjects\Uuid;

final readonly class MfaDisableUseCase
{
    public function __construct(
        private UserRepositoryInterface $userRepository,
        private RefreshTokenRepositoryInterface $refreshTokenRepository,
        private EventBusInterface $eventBus,
    ) {}

    public function execute(string $userId, string $password, string $code, string $ipAddress, string $currentSessionId): void
    {
        $user = $this->userRepository->findById(Uuid::fromString($userId));

        if ($user === null || $user->deletedAt() !== null) {
            throw new UserNotFoundException;
        }

        if (! $user->isMfaEnabled()) {
            throw new MfaNotEnabledException;
        }

        if (! $user->passwordHash()->verify($password)) {
            throw new InvalidCredentialsException;
        }

        $secret = $user->mfaSecret();

        if ($secret === null || $secret === '') {
            throw new MfaNotEnabledException;
        }

        $google2fa = new Google2FA;
        $google2fa->setAlgorithm('sha256');

        if (! $google2fa->verifyKey($secret, $code, 2)) {
            throw new MfaInvalidCodeException;
        }

        $user->disableMfa();
        $this->userRepository->update($user);

        $activeTokens = $this->refreshTokenRepository->findActiveByUser($user->id());

        foreach ($activeTokens as $token) {
            if ($token->sessionId()->toString() !== $currentSessionId) {
                $this->refreshTokenRepository->revoke($token->tokenHash(), 'mfa_disabled');
            }
        }

        $this->eventBus->dispatch(new MfaDisabled(
            userId: $user->id()->toString(),
            ip: $ipAddress,
            timestamp: new \DateTimeImmutable,
        ));
    }
}
