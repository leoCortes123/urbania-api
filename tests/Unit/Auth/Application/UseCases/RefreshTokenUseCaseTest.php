<?php

declare(strict_types=1);

use Urbania\Auth\Application\DTOs\RefreshTokenRequestDto;
use Urbania\Auth\Application\Services\JwtServiceInterface;
use Urbania\Auth\Application\UseCases\RefreshTokenUseCase;
use Urbania\Auth\Domain\Entities\RefreshTokenEntity;
use Urbania\Auth\Domain\Events\SuspiciousActivityDetected;
use Urbania\Auth\Domain\Exceptions\DeviceNotRecognizedException;
use Urbania\Auth\Domain\Exceptions\TokenExpiredException;
use Urbania\Auth\Domain\Exceptions\TokenInvalidException;
use Urbania\Auth\Domain\Repositories\RefreshTokenRepositoryInterface;
use Urbania\Auth\Domain\Repositories\UserRepositoryInterface;
use Urbania\Auth\Domain\ValueObjects\DeviceFingerprint;
use Urbania\Auth\Domain\ValueObjects\JwtToken;
use Urbania\Auth\Domain\ValueObjects\SessionId;
use Urbania\Shared\Application\Bus\EventBusInterface;
use Urbania\Shared\Domain\ValueObjects\Uuid;

beforeEach(function (): void {
    $this->refreshTokenRepository = Mockery::mock(RefreshTokenRepositoryInterface::class);
    $this->userRepository = Mockery::mock(UserRepositoryInterface::class);
    $this->jwtService = Mockery::mock(JwtServiceInterface::class);
    $this->eventBus = Mockery::mock(EventBusInterface::class);

    $this->userRepository->shouldReceive('findById')
        ->zeroOrMoreTimes()
        ->andReturn(null);

    $this->useCase = new RefreshTokenUseCase(
        $this->refreshTokenRepository,
        $this->userRepository,
        $this->jwtService,
        $this->eventBus,
    );
});

afterEach(function (): void {
    Mockery::close();
});

function createActiveRefreshToken(?DeviceFingerprint $deviceFingerprint = null, ?string $previousTokenHash = null): RefreshTokenEntity
{
    return RefreshTokenEntity::create(
        userId: Uuid::v7(),
        sessionId: SessionId::generate(),
        tokenHash: hash('sha256', 'raw-refresh-token'),
        tokenFamily: Uuid::v7(),
        expiresAt: (new DateTimeImmutable)->modify('+7 days'),
        previousTokenHash: $previousTokenHash,
        deviceFingerprint: $deviceFingerprint,
        deviceName: 'Test Device',
        ipAddress: '192.168.1.1',
        userAgent: 'Mozilla/5.0',
    );
}

it('rotates refresh token successfully', function (): void {
    $deviceFingerprint = DeviceFingerprint::calculate(
        userAgent: 'Mozilla/5.0',
        ip: '192.168.1.1',
        acceptLanguage: '',
        deviceName: 'Test Device',
    );
    $tokenEntity = createActiveRefreshToken($deviceFingerprint);
    $request = new RefreshTokenRequestDto(
        refreshToken: 'raw-refresh-token',
        userAgent: 'Mozilla/5.0',
        ipAddress: '192.168.1.1',
    );

    $this->refreshTokenRepository->shouldReceive('findByHash')
        ->once()
        ->andReturn($tokenEntity);

    $this->jwtService->shouldReceive('generateRefreshToken')
        ->once()
        ->andReturn('new-raw-refresh-token');

    $this->refreshTokenRepository->shouldReceive('revoke')
        ->once()
        ->with(hash('sha256', 'raw-refresh-token'), 'rotated');

    $this->refreshTokenRepository->shouldReceive('save')
        ->once()
        ->with(Mockery::on(function (RefreshTokenEntity $entity): bool {
            return $entity->previousTokenHash() === hash('sha256', 'raw-refresh-token')
                && $entity->tokenHash() === hash('sha256', 'new-raw-refresh-token');
        }));

    $this->jwtService->shouldReceive('generateAccessToken')
        ->once()
        ->andReturn(JwtToken::fromString('new-access-token'));

    $response = $this->useCase->execute($request);

    expect($response->accessToken)->toBe('new-access-token')
        ->and($response->refreshToken)->toBe('new-raw-refresh-token')
        ->and($response->tokenType)->toBe('bearer')
        ->and($response->expiresIn)->toBe(900);
});

it('throws TokenInvalidException when token is revoked', function (): void {
    $tokenEntity = createActiveRefreshToken();
    $tokenEntity->revoke('logout');

    $request = new RefreshTokenRequestDto(refreshToken: 'raw-refresh-token');

    $this->refreshTokenRepository->shouldReceive('findByHash')
        ->once()
        ->andReturn($tokenEntity);

    $this->refreshTokenRepository->shouldReceive('revokeAllByUser')
        ->once();

    $this->eventBus->shouldReceive('dispatch')
        ->once()
        ->with(Mockery::type(SuspiciousActivityDetected::class));

    $this->useCase->execute($request);
})->throws(TokenInvalidException::class);

it('throws TokenExpiredException when token is expired', function (): void {
    $tokenEntity = RefreshTokenEntity::create(
        userId: Uuid::v7(),
        sessionId: SessionId::generate(),
        tokenHash: hash('sha256', 'raw-refresh-token'),
        tokenFamily: Uuid::v7(),
        expiresAt: (new DateTimeImmutable)->modify('-1 day'),
    );

    $request = new RefreshTokenRequestDto(refreshToken: 'raw-refresh-token');

    $this->refreshTokenRepository->shouldReceive('findByHash')
        ->once()
        ->andReturn($tokenEntity);

    $this->useCase->execute($request);
})->throws(TokenExpiredException::class);

it('throws DeviceNotRecognizedException when fingerprint does not match', function (): void {
    $deviceFingerprint = DeviceFingerprint::calculate(
        userAgent: 'Mozilla/5.0',
        ip: '192.168.1.1',
        acceptLanguage: '',
        deviceName: 'Test Device',
    );
    $tokenEntity = createActiveRefreshToken($deviceFingerprint);

    $request = new RefreshTokenRequestDto(
        refreshToken: 'raw-refresh-token',
        userAgent: 'Mozilla/5.0',
        ipAddress: '10.0.0.1',
    );

    $this->refreshTokenRepository->shouldReceive('findByHash')
        ->once()
        ->andReturn($tokenEntity);

    $this->eventBus->shouldReceive('dispatch')
        ->once()
        ->with(Mockery::type(SuspiciousActivityDetected::class));

    $this->useCase->execute($request);
})->throws(DeviceNotRecognizedException::class);

it('detects replay and revokes token family', function (): void {
    $tokenEntity = createActiveRefreshToken(previousTokenHash: 'previous-hash');

    $request = new RefreshTokenRequestDto(refreshToken: 'raw-refresh-token');

    $this->refreshTokenRepository->shouldReceive('findByHash')
        ->once()
        ->andReturn($tokenEntity);

    $this->refreshTokenRepository->shouldReceive('revokeAllByUser')
        ->once()
        ->with($tokenEntity->userId());

    $capturedEvent = null;
    $this->eventBus->shouldReceive('dispatch')
        ->once()
        ->with(Mockery::type(SuspiciousActivityDetected::class))
        ->andReturnUsing(function (SuspiciousActivityDetected $event) use (&$capturedEvent): void {
            $capturedEvent = $event;
        });

    try {
        $this->useCase->execute($request);
    } catch (TokenInvalidException) {
        expect($capturedEvent)->toBeInstanceOf(SuspiciousActivityDetected::class)
            ->and($capturedEvent->activityType)->toBe('refresh_token_replay');

        return;
    }

    $this->fail('Expected TokenInvalidException was not thrown');
});

it('sets previous token hash on rotated token', function (): void {
    $deviceFingerprint = DeviceFingerprint::calculate(
        userAgent: 'Mozilla/5.0',
        ip: '192.168.1.1',
        acceptLanguage: '',
        deviceName: 'Test Device',
    );
    $tokenEntity = createActiveRefreshToken($deviceFingerprint);
    $request = new RefreshTokenRequestDto(
        refreshToken: 'raw-refresh-token',
        userAgent: 'Mozilla/5.0',
        ipAddress: '192.168.1.1',
    );

    $this->refreshTokenRepository->shouldReceive('findByHash')
        ->once()
        ->andReturn($tokenEntity);

    $this->refreshTokenRepository->shouldReceive('revoke')
        ->once()
        ->with(hash('sha256', 'raw-refresh-token'), 'rotated');

    $this->jwtService->shouldReceive('generateRefreshToken')
        ->once()
        ->andReturn('new-raw-refresh-token');

    $this->refreshTokenRepository->shouldReceive('save')
        ->once()
        ->with(Mockery::on(function (RefreshTokenEntity $entity): bool {
            return $entity->previousTokenHash() === hash('sha256', 'raw-refresh-token');
        }));

    $this->jwtService->shouldReceive('generateAccessToken')
        ->once()
        ->andReturn(JwtToken::fromString('new-access-token'));

    $this->useCase->execute($request);
});
