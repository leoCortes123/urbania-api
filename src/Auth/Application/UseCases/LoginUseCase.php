<?php

declare(strict_types=1);

namespace Urbania\Auth\Application\UseCases;

use Urbania\Auth\Application\DTOs\LoginRequestDto;
use Urbania\Auth\Application\DTOs\LoginResponseDto;
use Urbania\Auth\Application\DTOs\UserResponseDto;
use Urbania\Auth\Application\Services\JwtServiceInterface;
use Urbania\Auth\Domain\Entities\RefreshTokenEntity;
use Urbania\Auth\Domain\Entities\UserEntity;
use Urbania\Auth\Domain\Events\UserLoggedIn;
use Urbania\Auth\Domain\Exceptions\InvalidCredentialsException;
use Urbania\Auth\Domain\Exceptions\UserLockedException;
use Urbania\Auth\Domain\Repositories\RefreshTokenRepositoryInterface;
use Urbania\Auth\Domain\Repositories\UserRepositoryInterface;
use Urbania\Auth\Domain\ValueObjects\DeviceFingerprint;
use Urbania\Auth\Domain\ValueObjects\SessionId;
use Urbania\Shared\Application\Bus\EventBusInterface;
use Urbania\Shared\Domain\ValueObjects\Email;
use Urbania\Shared\Domain\ValueObjects\Uuid;

final readonly class LoginUseCase
{
    public function __construct(
        private UserRepositoryInterface $userRepository,
        private RefreshTokenRepositoryInterface $refreshTokenRepository,
        private JwtServiceInterface $jwtService,
        private EventBusInterface $eventBus,
    ) {}

    public function execute(LoginRequestDto $request): LoginResponseDto
    {
        $email = Email::fromString($request->email);
        $user = $this->userRepository->findByEmail($email);

        if ($user === null) {
            throw new InvalidCredentialsException;
        }

        if ($user->isLocked()) {
            throw new UserLockedException;
        }

        if (! $user->passwordHash()->verify($request->password)) {
            $user->recordFailedLogin();
            $this->userRepository->update($user);
            throw new InvalidCredentialsException;
        }

        if ($user->mustChangePassword()) {
            $limitedToken = $this->jwtService->generateAccessToken(
                userId: $user->id()->toString(),
                role: $user->role()->value,
                mfaVerified: false,
                sessionId: SessionId::generate(),
                deviceFingerprint: '',
                organizationId: $user->organizationId(),
                scope: 'change-password',
                ttl: 300,
            );

            return new LoginResponseDto(
                accessToken: '',
                refreshToken: '',
                tokenType: 'bearer',
                expiresIn: 300,
                user: $this->mapUserToDto($user),
                status: 'FORCE_PASSWORD_CHANGE',
                limitedToken: $limitedToken->toString(),
            );
        }

        if ($user->isMfaEnabled()) {
            $deviceFp = DeviceFingerprint::calculate(
                userAgent: $request->userAgent ?? '',
                ip: $request->ipAddress ?? 'unknown',
                acceptLanguage: $request->acceptLanguage ?? '',
                deviceName: $request->deviceName ?? 'Unknown Device',
            );

            $tempSessionId = SessionId::generate();
            $mfaToken = $this->jwtService->generateAccessToken(
                userId: $user->id()->toString(),
                role: $user->role()->value,
                mfaVerified: false,
                sessionId: $tempSessionId,
                deviceFingerprint: $deviceFp->toString(),
                organizationId: $user->organizationId(),
                scope: 'mfa-verify',
                ttl: 300,
            );

            return new LoginResponseDto(
                accessToken: '',
                refreshToken: '',
                tokenType: 'bearer',
                expiresIn: 300,
                user: $this->mapUserToDto($user),
                status: 'MFA_REQUIRED',
                limitedToken: $mfaToken->toString(),
            );
        }

        $user->recordSuccessfulLogin($request->ipAddress ?? 'unknown');
        $this->userRepository->update($user);

        $deviceFp = DeviceFingerprint::calculate(
            userAgent: $request->userAgent ?? '',
            ip: $request->ipAddress ?? 'unknown',
            acceptLanguage: $request->acceptLanguage ?? '',
            deviceName: $request->deviceName ?? 'Unknown Device',
        );

        $sessionId = SessionId::generate();

        $accessToken = $this->jwtService->generateAccessToken(
            userId: $user->id()->toString(),
            role: $user->role()->value,
            mfaVerified: false,
            sessionId: $sessionId,
            deviceFingerprint: $deviceFp->toString(),
            organizationId: $user->organizationId(),
        );

        $refreshTokenRaw = $this->jwtService->generateRefreshToken();
        $refreshTokenHash = hash('sha256', $refreshTokenRaw);
        $tokenFamily = Uuid::v7();

        $userAgent = $request->userAgent ?? '';
        $isMobile = (bool) preg_match('/Mobile|Android|iPhone|iPad|iPod|Windows Phone/i', $userAgent);
        $refreshTtl = $isMobile ? 2592000 : 604800;

        $refreshTokenEntity = RefreshTokenEntity::create(
            userId: $user->id(),
            sessionId: $sessionId,
            tokenHash: $refreshTokenHash,
            tokenFamily: $tokenFamily,
            expiresAt: (new \DateTimeImmutable)->modify("+{$refreshTtl} seconds"),
            previousTokenHash: null,
            deviceFingerprint: $deviceFp,
            deviceName: $request->deviceName,
            ipAddress: $request->ipAddress,
            userAgent: $request->userAgent,
        );
        $this->refreshTokenRepository->save($refreshTokenEntity);

        $this->eventBus->dispatch(new UserLoggedIn(
            userId: $user->id()->toString(),
            ip: $request->ipAddress ?? 'unknown',
            deviceFp: $deviceFp->toString(),
            mfaUsed: false,
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
            avatarUrl: $user->avatarUrl(),
            createdAt: $user->createdAt()->format('c'),
        );
    }
}
