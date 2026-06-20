<?php

declare(strict_types=1);

use App\Models\RefreshToken;
use Database\Factories\RefreshTokenFactory;
use Database\Factories\UserFactory;
use Illuminate\Support\Str;
use Urbania\Auth\Domain\ValueObjects\DeviceFingerprint;
use Urbania\Auth\Domain\ValueObjects\SessionId;
use Urbania\Auth\Infrastructure\Mappers\RefreshTokenMapper;
use Urbania\Shared\Domain\ValueObjects\Uuid;

beforeEach(function (): void {
    $this->mapper = new RefreshTokenMapper;
    $this->user = UserFactory::new()->create();
});

it('converts an eloquent model to a refresh token entity', function (): void {
    $model = RefreshTokenFactory::new()->for($this->user)->create();

    $entity = $this->mapper->toDomain($model);

    expect($entity->id()->toString())->toBe($model->id)
        ->and($entity->userId()->toString())->toBe($model->user_id)
        ->and($entity->sessionId()->toString())->toBe($model->session_id)
        ->and($entity->tokenHash())->toBe($model->token_hash)
        ->and($entity->tokenFamily()->toString())->toBe($model->token_family)
        ->and($entity->deviceFingerprint()?->toString())->toBe($model->device_fingerprint)
        ->and($entity->expiresAt()->getTimestamp())->toBe($model->expires_at->getTimestamp())
        ->and($entity->createdAt()->getTimestamp())->toBe($model->created_at->getTimestamp());
});

it('converts a refresh token entity to a persistence array', function (): void {
    $model = RefreshTokenFactory::new()->for($this->user)->create();
    $entity = $this->mapper->toDomain($model);

    $persistence = $this->mapper->toPersistence($entity);

    expect($persistence['id'])->toBe($model->id)
        ->and($persistence['user_id'])->toBe($model->user_id)
        ->and($persistence['session_id'])->toBe($model->session_id)
        ->and($persistence['token_hash'])->toBe($model->token_hash)
        ->and($persistence['token_family'])->toBe($model->token_family);
});

it('preserves all values through bidirectional mapping', function (): void {
    $model = RefreshTokenFactory::new()->for($this->user)->used()->create();

    $entity = $this->mapper->toDomain($model);
    $persistence = $this->mapper->toPersistence($entity);

    expect($persistence['id'])->toBe($model->id)
        ->and($persistence['user_id'])->toBe($model->user_id)
        ->and($persistence['session_id'])->toBe($model->session_id)
        ->and($persistence['token_hash'])->toBe($model->token_hash)
        ->and($persistence['token_family'])->toBe($model->token_family)
        ->and($persistence['device_fingerprint'])->toBe($model->device_fingerprint)
        ->and($persistence['device_name'])->toBe($model->device_name)
        ->and($persistence['ip_address'])->toBe($model->ip_address)
        ->and($persistence['user_agent'])->toBe($model->user_agent)
        ->and($persistence['expires_at'])->toBe($model->expires_at->format('Y-m-d H:i:s'))
        ->and($persistence['last_used_at'])->toBe($model->last_used_at->format('Y-m-d H:i:s'))
        ->and($persistence['created_at'])->toBe($model->created_at->format('Y-m-d H:i:s'));
});

it('maps device fingerprint correctly including null', function (): void {
    $withFp = RefreshTokenFactory::new()->for($this->user)->create();

    $withoutFp = new RefreshToken([
        'id' => (string) Str::orderedUuid(),
        'user_id' => $this->user->id,
        'session_id' => (string) Str::orderedUuid(),
        'token_hash' => hash('sha256', 'test'),
        'token_family' => (string) Str::orderedUuid(),
        'device_fingerprint' => null,
        'expires_at' => now()->addDay(),
        'created_at' => now(),
    ]);

    $entityWith = $this->mapper->toDomain($withFp);
    $entityWithout = $this->mapper->toDomain($withoutFp);

    expect($entityWith->deviceFingerprint())->toBeInstanceOf(DeviceFingerprint::class)
        ->and($entityWithout->deviceFingerprint())->toBeNull();
});

it('maps session id value object correctly', function (): void {
    $model = RefreshTokenFactory::new()->for($this->user)->create();

    $entity = $this->mapper->toDomain($model);

    expect($entity->sessionId())->toBeInstanceOf(SessionId::class)
        ->and($entity->sessionId()->toString())->toBe($model->session_id)
        ->and($entity->userId())->toBeInstanceOf(Uuid::class)
        ->and($entity->tokenFamily())->toBeInstanceOf(Uuid::class);
});
