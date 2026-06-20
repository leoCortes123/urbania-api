<?php

declare(strict_types=1);

namespace Urbania\Auth\Application\UseCases;

use Urbania\Auth\Application\DTOs\SessionResponseDto;
use Urbania\Auth\Domain\Repositories\RefreshTokenRepositoryInterface;
use Urbania\Shared\Domain\ValueObjects\Uuid;

final readonly class ListSessionsUseCase
{
    public function __construct(
        private RefreshTokenRepositoryInterface $refreshTokenRepository,
    ) {}

    /**
     * @return list<SessionResponseDto>
     */
    public function execute(string $userId, string $currentSessionId): array
    {
        $tokens = $this->refreshTokenRepository->findActiveByUser(Uuid::fromString($userId));

        $sessions = [];
        $seen = [];

        foreach ($tokens as $token) {
            $sid = $token->sessionId()->toString();

            if (isset($seen[$sid])) {
                continue;
            }

            $seen[$sid] = true;

            $sessions[] = new SessionResponseDto(
                sessionId: $sid,
                deviceName: $token->deviceName(),
                deviceFingerprint: $token->deviceFingerprint()?->toString() ?? '',
                ipAddress: $token->ipAddress() ?? '',
                lastUsedAt: $token->lastUsedAt()?->format('c') ?? $token->createdAt()->format('c'),
                createdAt: $token->createdAt()->format('c'),
                isCurrent: $sid === $currentSessionId,
            );
        }

        return $sessions;
    }
}
