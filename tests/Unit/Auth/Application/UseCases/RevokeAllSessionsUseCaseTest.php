<?php

declare(strict_types=1);

use Urbania\Auth\Application\UseCases\RevokeAllSessionsUseCase;
use Urbania\Auth\Domain\Entities\RefreshTokenEntity;
use Urbania\Auth\Domain\Repositories\RefreshTokenRepositoryInterface;
use Urbania\Auth\Domain\ValueObjects\SessionId;
use Urbania\Shared\Domain\ValueObjects\Uuid;

beforeEach(function (): void {
    $this->repository = Mockery::mock(RefreshTokenRepositoryInterface::class);
    $this->useCase = new RevokeAllSessionsUseCase($this->repository);
});

afterEach(function (): void {
    Mockery::close();
});

it('revokes all sessions except current', function (): void {
    $userId = Uuid::v7()->toString();
    $currentSessionId = SessionId::generate()->toString();
    $otherSessionId = SessionId::generate()->toString();

    $currentToken = RefreshTokenEntity::create(
        userId: Uuid::fromString($userId),
        sessionId: SessionId::fromString($currentSessionId),
        tokenHash: hash('sha256', random_bytes(32)),
        tokenFamily: Uuid::v7(),
        expiresAt: (new DateTimeImmutable)->modify('+7 days'),
    );

    $otherToken = RefreshTokenEntity::create(
        userId: Uuid::fromString($userId),
        sessionId: SessionId::fromString($otherSessionId),
        tokenHash: hash('sha256', random_bytes(32)),
        tokenFamily: Uuid::v7(),
        expiresAt: (new DateTimeImmutable)->modify('+7 days'),
    );

    $this->repository->shouldReceive('findActiveByUser')
        ->once()
        ->andReturn([$currentToken, $otherToken]);

    $this->repository->shouldReceive('revoke')
        ->once()
        ->with($otherToken->tokenHash(), 'session_revoked');

    $this->useCase->execute($userId, $currentSessionId);
});

it('does not revoke current session', function (): void {
    $userId = Uuid::v7()->toString();
    $currentSessionId = SessionId::generate()->toString();

    $currentToken = RefreshTokenEntity::create(
        userId: Uuid::fromString($userId),
        sessionId: SessionId::fromString($currentSessionId),
        tokenHash: hash('sha256', random_bytes(32)),
        tokenFamily: Uuid::v7(),
        expiresAt: (new DateTimeImmutable)->modify('+7 days'),
    );

    $this->repository->shouldReceive('findActiveByUser')
        ->once()
        ->andReturn([$currentToken]);

    $this->repository->shouldNotReceive('revoke');

    $this->useCase->execute($userId, $currentSessionId);
});
