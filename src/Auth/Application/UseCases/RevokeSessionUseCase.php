<?php

declare(strict_types=1);

namespace Urbania\Auth\Application\UseCases;

use Urbania\Auth\Domain\Exceptions\SessionNotFoundException;
use Urbania\Auth\Domain\Repositories\RefreshTokenRepositoryInterface;
use Urbania\Shared\Domain\ValueObjects\Uuid;

final readonly class RevokeSessionUseCase
{
    public function __construct(
        private RefreshTokenRepositoryInterface $refreshTokenRepository,
    ) {}

    public function execute(string $userId, string $sessionId): void
    {
        $tokens = $this->refreshTokenRepository->findActiveByUser(Uuid::fromString($userId));
        $found = false;

        foreach ($tokens as $token) {
            if ($token->sessionId()->toString() === $sessionId) {
                $this->refreshTokenRepository->revoke($token->tokenHash(), 'session_revoked');
                $found = true;
            }
        }

        if (! $found) {
            throw new SessionNotFoundException;
        }
    }
}
