<?php

declare(strict_types=1);

use Urbania\Auth\Application\DTOs\LogoutRequestDto;
use Urbania\Auth\Application\UseCases\LogoutUseCase;
use Urbania\Auth\Domain\Entities\RefreshTokenEntity;
use Urbania\Auth\Domain\Events\UserLoggedOut;
use Urbania\Auth\Domain\Exceptions\TokenInvalidException;
use Urbania\Auth\Domain\Repositories\RefreshTokenRepositoryInterface;
use Urbania\Auth\Domain\ValueObjects\SessionId;
use Urbania\Shared\Application\Bus\EventBusInterface;
use Urbania\Shared\Domain\ValueObjects\Uuid;

beforeEach(function (): void {
    $this->refreshTokenRepository = Mockery::mock(RefreshTokenRepositoryInterface::class);
    $this->eventBus = Mockery::mock(EventBusInterface::class);

    $this->useCase = new LogoutUseCase(
        $this->refreshTokenRepository,
        $this->eventBus,
    );
});

afterEach(function (): void {
    Mockery::close();
});

it('revokes refresh token on logout', function (): void {
    $rawToken = 'raw-refresh-token';
    $tokenHash = hash('sha256', $rawToken);

    $tokenEntity = RefreshTokenEntity::create(
        userId: Uuid::v7(),
        sessionId: SessionId::generate(),
        tokenHash: $tokenHash,
        tokenFamily: Uuid::v7(),
        expiresAt: (new DateTimeImmutable)->modify('+7 days'),
    );

    $request = new LogoutRequestDto(refreshToken: $rawToken);

    $this->refreshTokenRepository->shouldReceive('findByHash')
        ->once()
        ->with($tokenHash)
        ->andReturn($tokenEntity);

    $this->refreshTokenRepository->shouldReceive('revoke')
        ->once()
        ->with($tokenHash, 'logout');

    $this->eventBus->shouldReceive('dispatch')
        ->once()
        ->with(Mockery::type(UserLoggedOut::class));

    $this->useCase->execute($request);

    expect($tokenEntity->isRevoked())->toBeTrue()
        ->and($tokenEntity->revocationReason())->toBe('logout');
});

it('throws TokenInvalidException when token is not found', function (): void {
    $request = new LogoutRequestDto(refreshToken: 'invalid-token');

    $this->refreshTokenRepository->shouldReceive('findByHash')
        ->once()
        ->andReturn(null);

    $this->useCase->execute($request);
})->throws(TokenInvalidException::class);

it('dispatches UserLoggedOut event', function (): void {
    $rawToken = 'raw-refresh-token';
    $tokenHash = hash('sha256', $rawToken);

    $tokenEntity = RefreshTokenEntity::create(
        userId: Uuid::v7(),
        sessionId: SessionId::generate(),
        tokenHash: $tokenHash,
        tokenFamily: Uuid::v7(),
        expiresAt: (new DateTimeImmutable)->modify('+7 days'),
    );

    $request = new LogoutRequestDto(refreshToken: $rawToken);

    $this->refreshTokenRepository->shouldReceive('findByHash')
        ->once()
        ->andReturn($tokenEntity);

    $this->refreshTokenRepository->shouldReceive('revoke')
        ->once();

    $capturedEvent = null;
    $this->eventBus->shouldReceive('dispatch')
        ->once()
        ->with(Mockery::type(UserLoggedOut::class))
        ->andReturnUsing(function (UserLoggedOut $event) use (&$capturedEvent): void {
            $capturedEvent = $event;
        });

    $this->useCase->execute($request);

    expect($capturedEvent)->toBeInstanceOf(UserLoggedOut::class)
        ->and($capturedEvent->userId)->toBe($tokenEntity->userId()->toString())
        ->and($capturedEvent->sessionId)->toBe($tokenEntity->sessionId()->toString());
});

it('computes sha256 hash correctly', function (): void {
    $rawToken = 'raw-refresh-token';
    $expectedHash = hash('sha256', $rawToken);

    $tokenEntity = RefreshTokenEntity::create(
        userId: Uuid::v7(),
        sessionId: SessionId::generate(),
        tokenHash: $expectedHash,
        tokenFamily: Uuid::v7(),
        expiresAt: (new DateTimeImmutable)->modify('+7 days'),
    );

    $request = new LogoutRequestDto(refreshToken: $rawToken);

    $this->refreshTokenRepository->shouldReceive('findByHash')
        ->once()
        ->with($expectedHash)
        ->andReturn($tokenEntity);

    $this->refreshTokenRepository->shouldReceive('revoke')
        ->once();

    $this->eventBus->shouldReceive('dispatch')
        ->once();

    $this->useCase->execute($request);
});
