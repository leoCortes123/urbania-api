<?php

declare(strict_types=1);

use App\Models\RefreshToken as RefreshTokenModel;
use App\Models\User;
use Database\Factories\UserFactory;
use Urbania\Auth\Domain\Entities\RefreshTokenEntity;
use Urbania\Auth\Domain\Repositories\RefreshTokenRepositoryInterface;
use Urbania\Auth\Domain\ValueObjects\DeviceFingerprint;
use Urbania\Auth\Domain\ValueObjects\SessionId;
use Urbania\Auth\Infrastructure\Mappers\RefreshTokenMapper;
use Urbania\Auth\Infrastructure\Persistence\EloquentRefreshTokenRepository;
use Urbania\Shared\Domain\ValueObjects\Uuid;

beforeEach(function (): void {
    $this->repository = new EloquentRefreshTokenRepository(new RefreshTokenMapper);
    $this->user = UserFactory::new()->create();
});

it('persists a refresh token with all fields', function (): void {
    $entity = buildToken();

    $this->repository->save($entity);

    $model = RefreshTokenModel::find($entity->id()->toString());

    expect($model)->not->toBeNull()
        ->and($model->token_hash)->toBe($entity->tokenHash())
        ->and($model->user_id)->toBe($this->user->id)
        ->and($model->session_id)->toBe($entity->sessionId()->toString())
        ->and($model->device_fingerprint)->toBe($entity->deviceFingerprint()?->toString())
        ->and($model->token_family)->toBe($entity->tokenFamily()->toString());
});

it('finds a token by hash', function (): void {
    $entity = buildToken();
    $this->repository->save($entity);

    $found = $this->repository->findByHash($entity->tokenHash());

    expect($found)->toBeInstanceOf(RefreshTokenEntity::class)
        ->and($found->id()->toString())->toBe($entity->id()->toString());
});

it('returns null for unknown hash', function (): void {
    expect($this->repository->findByHash(hash('sha256', 'unknown')))->toBeNull();
});

it('revokes a token with a reason', function (): void {
    $entity = buildToken();
    $this->repository->save($entity);

    $this->repository->revoke($entity->tokenHash(), 'logout');

    $found = $this->repository->findByHash($entity->tokenHash());

    expect($found->isRevoked())->toBeTrue()
        ->and($found->revocationReason())->toBe('logout')
        ->and(RefreshTokenModel::find($entity->id()->toString())->revoked_at)->not->toBeNull();
});

it('revokes all active tokens for a user', function (): void {
    $otherUser = UserFactory::new()->create();

    $tokenA = buildToken();
    $tokenB = buildToken();
    $otherToken = buildToken($otherUser);

    $this->repository->save($tokenA);
    $this->repository->save($tokenB);
    $this->repository->save($otherToken);

    $this->repository->revokeAllByUser(Uuid::fromString($this->user->id));

    expect($this->repository->findByHash($tokenA->tokenHash())->isRevoked())->toBeTrue()
        ->and($this->repository->findByHash($tokenB->tokenHash())->isRevoked())->toBeTrue()
        ->and($this->repository->findByHash($otherToken->tokenHash())->isRevoked())->toBeFalse();
});

it('finds only active tokens for a user', function (): void {
    $active = buildToken();
    $revoked = buildToken();
    $expired = buildToken(expired: true);

    $this->repository->save($active);
    $this->repository->save($revoked);
    $this->repository->save($expired);

    $this->repository->revoke($revoked->tokenHash(), 'logout');

    $activeTokens = $this->repository->findActiveByUser(Uuid::fromString($this->user->id));

    expect($activeTokens)->toHaveCount(1)
        ->and($activeTokens[0]->tokenHash())->toBe($active->tokenHash());
});

it('checks hash existence correctly', function (): void {
    $entity = buildToken();
    $this->repository->save($entity);

    expect($this->repository->existsByHash($entity->tokenHash()))->toBeTrue()
        ->and($this->repository->existsByHash(hash('sha256', 'missing')))->toBeFalse();
});

it('preserves device fingerprint and token family through persistence', function (): void {
    $entity = buildToken();
    $this->repository->save($entity);

    $found = $this->repository->findByHash($entity->tokenHash());

    expect($found->deviceFingerprint()?->toString())->toBe($entity->deviceFingerprint()?->toString())
        ->and($found->tokenFamily()->toString())->toBe($entity->tokenFamily()->toString());
});

it('resolves repository from container', function (): void {
    expect(app(RefreshTokenRepositoryInterface::class))->toBeInstanceOf(EloquentRefreshTokenRepository::class);
});

function buildToken(?User $user = null, bool $expired = false): RefreshTokenEntity
{
    $userId = Uuid::fromString($user?->id ?? test()->user->id);
    $fingerprint = DeviceFingerprint::fromHash(hash('sha256', 'Mozilla/5.0'));

    return RefreshTokenEntity::create(
        userId: $userId,
        sessionId: SessionId::generate(),
        tokenHash: hash('sha256', fake()->uuid()),
        tokenFamily: Uuid::v7(),
        expiresAt: $expired ? now()->subDay()->toDateTimeImmutable() : now()->addDay()->toDateTimeImmutable(),
        previousTokenHash: null,
        deviceFingerprint: $fingerprint,
        deviceName: 'Test Device',
        ipAddress: '127.0.0.1',
        userAgent: 'Mozilla/5.0',
    );
}
