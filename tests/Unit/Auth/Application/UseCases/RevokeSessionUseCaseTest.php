<?php

declare(strict_types=1);

use Urbania\Auth\Application\UseCases\RevokeSessionUseCase;
use Urbania\Auth\Domain\Entities\RefreshTokenEntity;
use Urbania\Auth\Domain\Exceptions\SessionNotFoundException;
use Urbania\Auth\Domain\Repositories\RefreshTokenRepositoryInterface;
use Urbania\Auth\Domain\ValueObjects\SessionId;
use Urbania\Shared\Domain\ValueObjects\Uuid;

function createRevocableToken(string $sessionId): RefreshTokenEntity
{
    return RefreshTokenEntity::create(
        userId: Uuid::v7(),
        sessionId: SessionId::fromString($sessionId),
        tokenHash: hash('sha256', random_bytes(32)),
        tokenFamily: Uuid::v7(),
        expiresAt: (new DateTimeImmutable)->modify('+7 days'),
    );
}

beforeEach(function (): void {
    $this->repository = Mockery::mock(RefreshTokenRepositoryInterface::class);
    $this->useCase = new RevokeSessionUseCase($this->repository);
});

afterEach(function (): void {
    Mockery::close();
});

it('revokes a specific session', function (): void {
    $userId = Uuid::v7()->toString();
    $sessionId = SessionId::generate()->toString();
    $token = createRevocableToken($sessionId);

    $this->repository->shouldReceive('findActiveByUser')
        ->once()
        ->with(Mockery::on(fn (Uuid $id): bool => $id->toString() === $userId))
        ->andReturn([$token]);

    $this->repository->shouldReceive('revoke')
        ->once()
        ->with($token->tokenHash(), 'session_revoked');

    $this->useCase->execute($userId, $sessionId);
});

it('throws SessionNotFoundException when session is not found', function (): void {
    $userId = Uuid::v7()->toString();
    $sessionId = SessionId::generate()->toString();
    $otherSessionId = SessionId::generate()->toString();
    $token = createRevocableToken($otherSessionId);

    $this->repository->shouldReceive('findActiveByUser')
        ->once()
        ->andReturn([$token]);

    $this->useCase->execute($userId, $sessionId);
})->throws(SessionNotFoundException::class);
