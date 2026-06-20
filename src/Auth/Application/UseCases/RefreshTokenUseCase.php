<?php

declare(strict_types=1);

namespace Urbania\Auth\Application\UseCases;

use Urbania\Auth\Application\DTOs\RefreshTokenRequestDto;
use Urbania\Auth\Application\DTOs\TokenResponseDto;
use Urbania\Auth\Application\Services\JwtServiceInterface;
use Urbania\Auth\Domain\Entities\RefreshTokenEntity;
use Urbania\Auth\Domain\Events\SuspiciousActivityDetected;
use Urbania\Auth\Domain\Exceptions\DeviceNotRecognizedException;
use Urbania\Auth\Domain\Exceptions\TokenExpiredException;
use Urbania\Auth\Domain\Exceptions\TokenInvalidException;
use Urbania\Auth\Domain\Repositories\RefreshTokenRepositoryInterface;
use Urbania\Auth\Domain\ValueObjects\DeviceFingerprint;
use Urbania\Shared\Application\Bus\EventBusInterface;

final readonly class RefreshTokenUseCase
{
    public function __construct(
        private RefreshTokenRepositoryInterface $refreshTokenRepository,
        private JwtServiceInterface $jwtService,
        private EventBusInterface $eventBus,
    ) {}

    public function execute(RefreshTokenRequestDto $request): TokenResponseDto
    {
        $tokenHash = hash('sha256', $request->refreshToken);
        $tokenEntity = $this->refreshTokenRepository->findByHash($tokenHash);

        if ($tokenEntity === null) {
            throw new TokenInvalidException;
        }

        if ($tokenEntity->isRevoked()) {
            $this->handleReplayDetection($tokenEntity, $request);
            throw new TokenInvalidException;
        }

        if ($tokenEntity->isExpired()) {
            $tokenEntity->revoke('expired');
            throw new TokenExpiredException;
        }

        if ($tokenEntity->deviceFingerprint() !== null && $request->userAgent !== null) {
            $currentFp = DeviceFingerprint::calculate(
                userAgent: $request->userAgent,
                ip: $request->ipAddress ?? 'unknown',
                acceptLanguage: '',
                deviceName: $tokenEntity->deviceName() ?? '',
            );

            if (! $tokenEntity->deviceFingerprint()->equals($currentFp)) {
                throw new DeviceNotRecognizedException;
            }
        }

        if ($tokenEntity->previousTokenHash() !== null) {
            $this->handleReplayDetection($tokenEntity, $request);
            throw new TokenInvalidException;
        }

        $tokenEntity->revoke('rotated');

        $newRefreshTokenRaw = $this->jwtService->generateRefreshToken();
        $newTokenHash = hash('sha256', $newRefreshTokenRaw);

        $userAgent = $request->userAgent ?? '';
        $isMobile = (bool) preg_match('/Mobile|Android|iPhone|iPad|iPod|Windows Phone/i', $userAgent);
        $refreshTtl = $isMobile ? 2592000 : 604800;

        $newTokenEntity = RefreshTokenEntity::create(
            userId: $tokenEntity->userId(),
            sessionId: $tokenEntity->sessionId(),
            tokenHash: $newTokenHash,
            tokenFamily: $tokenEntity->tokenFamily(),
            expiresAt: (new \DateTimeImmutable)->modify("+{$refreshTtl} seconds"),
            previousTokenHash: $tokenHash,
            deviceFingerprint: $tokenEntity->deviceFingerprint(),
            deviceName: $tokenEntity->deviceName(),
            ipAddress: $request->ipAddress,
            userAgent: $request->userAgent,
        );
        $this->refreshTokenRepository->save($newTokenEntity);

        $tokenEntity->markUsed();

        $accessToken = $this->jwtService->generateAccessToken(
            userId: $tokenEntity->userId()->toString(),
            role: 'user',
            mfaVerified: false,
            sessionId: $tokenEntity->sessionId(),
            deviceFingerprint: $tokenEntity->deviceFingerprint()?->toString() ?? '',
        );

        return new TokenResponseDto(
            accessToken: $accessToken->toString(),
            refreshToken: $newRefreshTokenRaw,
            tokenType: 'bearer',
            expiresIn: 900,
        );
    }

    private function handleReplayDetection(RefreshTokenEntity $tokenEntity, RefreshTokenRequestDto $request): void
    {
        $this->refreshTokenRepository->revokeAllByUser($tokenEntity->userId());

        $this->eventBus->dispatch(new SuspiciousActivityDetected(
            userId: $tokenEntity->userId()->toString(),
            activityType: 'refresh_token_replay',
            ip: $request->ipAddress ?? 'unknown',
            details: [
                'tokenFamily' => $tokenEntity->tokenFamily()->toString(),
                'attemptedAt' => (new \DateTimeImmutable)->format('c'),
            ],
            timestamp: new \DateTimeImmutable,
        ));
    }
}
