<?php

declare(strict_types=1);

namespace Urbania\Auth\Application\UseCases;

use Urbania\Auth\Domain\Repositories\RefreshTokenRepositoryInterface;
use Urbania\Shared\Domain\ValueObjects\Uuid;

final readonly class RevokeAllSessionsUseCase
{
    public function __construct(
        private RefreshTokenRepositoryInterface $refreshTokenRepository,
    ) {}

    public function execute(string $userId, string $currentSessionId): void
    {
        $tokens = $this->refreshTokenRepository->findActiveByUser(Uuid::fromString($userId));

        foreach ($tokens as $token) {
            if ($token->sessionId()->toString() !== $currentSessionId) {
                $this->refreshTokenRepository->revoke($token->tokenHash(), 'session_revoked');
            }
        }
    }
}
