<?php

declare(strict_types=1);

namespace Urbania\Auth\Application\UseCases;

use Urbania\Auth\Application\DTOs\LoginResponseDto;
use Urbania\Auth\Application\DTOs\UserResponseDto;
use Urbania\Auth\Application\Services\JwtServiceInterface;
use Urbania\Auth\Domain\Entities\RefreshTokenEntity;
use Urbania\Auth\Domain\Entities\UserEntity;
use Urbania\Auth\Domain\Events\UserLoggedIn;
use Urbania\Auth\Domain\Exceptions\MfaInvalidCodeException;
use Urbania\Auth\Domain\Exceptions\TokenInvalidException;
use Urbania\Auth\Domain\Exceptions\UserNotFoundException;
use Urbania\Auth\Domain\Repositories\RefreshTokenRepositoryInterface;
use Urbania\Auth\Domain\Repositories\UserRepositoryInterface;
use Urbania\Auth\Domain\ValueObjects\DeviceFingerprint;
use Urbania\Auth\Domain\ValueObjects\SessionId;
use Urbania\Shared\Application\Bus\EventBusInterface;
use Urbania\Shared\Domain\ValueObjects\Uuid;

final readonly class MfaVerifyBackupUseCase
{
    public function __construct(
        private UserRepositoryInterface $userRepository,
        private JwtServiceInterface $jwtService,
        private RefreshTokenRepositoryInterface $refreshTokenRepository,
        private EventBusInterface $eventBus,
    ) {}

    public function execute(string $mfaToken, string $code, string $userAgent, string $ipAddress): LoginResponseDto
    {
        if (! $this->jwtService->validate($mfaToken)) {
            throw new TokenInvalidException('MFA token is invalid or expired');
        }

        $payload = $this->jwtService->decode($mfaToken);
        $userId = $payload['sub'] ?? null;

        if (! is_string($userId) || $userId === '') {
            throw new TokenInvalidException('MFA token payload is invalid');
        }

        $user = $this->userRepository->findById(Uuid::fromString($userId));

        if ($user === null || $user->deletedAt() !== null) {
            throw new UserNotFoundException;
        }

        if (! $user->validateBackupCode($code)) {
            throw new MfaInvalidCodeException;
        }

        $user->removeBackupCode($code);
        $this->userRepository->update($user);

        return $this->issueTokens($user, $userAgent, $ipAddress);
    }

    private function issueTokens(UserEntity $user, string $userAgent, string $ipAddress): LoginResponseDto
    {
        $user->recordSuccessfulLogin($ipAddress);
        $this->userRepository->update($user);

        $deviceFp = DeviceFingerprint::calculate(
            userAgent: $userAgent,
            ip: $ipAddress,
            acceptLanguage: '',
            deviceName: '',
        );

        $sessionId = SessionId::generate();

        $accessToken = $this->jwtService->generateAccessToken(
            userId: $user->id()->toString(),
            role: $user->role()->value,
            mfaVerified: true,
            sessionId: $sessionId,
            deviceFingerprint: $deviceFp->toString(),
            organizationId: $user->organizationId(),
        );

        $refreshTokenRaw = $this->jwtService->generateRefreshToken();
        $tokenHash = hash('sha256', $refreshTokenRaw);
        $tokenFamily = Uuid::v7();

        $refreshEntity = RefreshTokenEntity::create(
            userId: $user->id(),
            sessionId: $sessionId,
            tokenHash: $tokenHash,
            tokenFamily: $tokenFamily,
            expiresAt: (new \DateTimeImmutable)->modify('+7 days'),
            previousTokenHash: null,
            deviceFingerprint: $deviceFp,
            deviceName: '',
            ipAddress: $ipAddress,
            userAgent: $userAgent,
        );
        $this->refreshTokenRepository->save($refreshEntity);

        $this->eventBus->dispatch(new UserLoggedIn(
            userId: $user->id()->toString(),
            ip: $ipAddress,
            deviceFp: $deviceFp->toString(),
            mfaUsed: true,
            timestamp: new \DateTimeImmutable,
        ));

        return new LoginResponseDto(
            accessToken: $accessToken->toString(),
            refreshToken: $refreshTokenRaw,
            tokenType: 'bearer',
            expiresIn: 900,
            user: $this->mapUserToDto($user),
            status: null,
            limitedToken: null,
        );
    }

    private function mapUserToDto(UserEntity $user): UserResponseDto
    {
        return new UserResponseDto(
            id: $user->id()->toString(),
            name: $user->name(),
            email: $user->email()->toString(),
            phone: $user->phone(),
            role: $user->role()->value,
            status: $user->status()->value,
            avatarUrl: null,
            createdAt: $user->createdAt()->format('c'),
        );
    }
}
