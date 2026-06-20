<?php

declare(strict_types=1);

use Urbania\Auth\Application\DTOs\SessionResponseDto;
use Urbania\Auth\Application\UseCases\ListSessionsUseCase;
use Urbania\Auth\Domain\Entities\RefreshTokenEntity;
use Urbania\Auth\Domain\Repositories\RefreshTokenRepositoryInterface;
use Urbania\Auth\Domain\ValueObjects\DeviceFingerprint;
use Urbania\Auth\Domain\ValueObjects\SessionId;
use Urbania\Shared\Domain\ValueObjects\Uuid;

function createSessionToken(string $sessionId, ?DeviceFingerprint $fp = null, ?string $deviceName = 'Test Device'): RefreshTokenEntity
{
    return RefreshTokenEntity::create(
        userId: Uuid::v7(),
        sessionId: SessionId::fromString($sessionId),
        tokenHash: hash('sha256', random_bytes(32)),
        tokenFamily: Uuid::v7(),
        expiresAt: (new DateTimeImmutable)->modify('+7 days'),
        previousTokenHash: null,
        deviceFingerprint: $fp,
        deviceName: $deviceName,
        ipAddress: '192.168.1.1',
        userAgent: 'Mozilla/5.0',
    );
}

beforeEach(function (): void {
    $this->repository = Mockery::mock(RefreshTokenRepositoryInterface::class);
    $this->useCase = new ListSessionsUseCase($this->repository);
});

afterEach(function (): void {
    Mockery::close();
});

it('groups tokens by session id', function (): void {
    $userId = Uuid::v7()->toString();
    $sessionId = SessionId::generate()->toString();
    $deviceFingerprint = DeviceFingerprint::calculate(
        userAgent: 'Mozilla/5.0',
        ip: '192.168.1.1',
        acceptLanguage: '',
        deviceName: 'Test Device',
    );

    $tokens = [
        createSessionToken($sessionId, $deviceFingerprint),
        createSessionToken($sessionId, $deviceFingerprint),
    ];

    $this->repository->shouldReceive('findActiveByUser')
        ->once()
        ->andReturn($tokens);

    $sessions = $this->useCase->execute($userId, $sessionId);

    expect($sessions)->toHaveCount(1)
        ->and($sessions[0])->toBeInstanceOf(SessionResponseDto::class)
        ->and($sessions[0]->sessionId)->toBe($sessionId);
});

it('marks current session correctly', function (): void {
    $userId = Uuid::v7()->toString();
    $currentSessionId = SessionId::generate()->toString();
    $otherSessionId = SessionId::generate()->toString();

    $tokens = [
        createSessionToken($currentSessionId),
        createSessionToken($otherSessionId),
    ];

    $this->repository->shouldReceive('findActiveByUser')
        ->once()
        ->andReturn($tokens);

    $sessions = $this->useCase->execute($userId, $currentSessionId);

    $current = array_values(array_filter($sessions, fn (SessionResponseDto $s): bool => $s->sessionId === $currentSessionId))[0];
    $other = array_values(array_filter($sessions, fn (SessionResponseDto $s): bool => $s->sessionId === $otherSessionId))[0];

    expect($current->isCurrent)->toBeTrue()
        ->and($other->isCurrent)->toBeFalse();
});

it('returns empty array when no active sessions', function (): void {
    $userId = Uuid::v7()->toString();

    $this->repository->shouldReceive('findActiveByUser')
        ->once()
        ->andReturn([]);

    $sessions = $this->useCase->execute($userId, SessionId::generate()->toString());

    expect($sessions)->toBeEmpty();
});
