<?php

declare(strict_types=1);

use Urbania\Auth\Domain\Entities\RefreshTokenEntity;
use Urbania\Auth\Domain\ValueObjects\DeviceFingerprint;
use Urbania\Auth\Domain\ValueObjects\SessionId;
use Urbania\Shared\Domain\ValueObjects\Uuid;

function createRefreshToken(?DateTimeImmutable $expiresAt = null): RefreshTokenEntity
{
    return RefreshTokenEntity::create(
        userId: Uuid::v7(),
        sessionId: SessionId::generate(),
        tokenHash: hash('sha256', 'token-value'),
        tokenFamily: Uuid::v7(),
        expiresAt: $expiresAt ?? (new DateTimeImmutable)->modify('+30 days'),
        previousTokenHash: hash('sha256', 'previous-token'),
        deviceFingerprint: DeviceFingerprint::calculate('Mozilla/5.0', '192.168.1.1', 'en-US', 'Chrome'),
        deviceName: 'Chrome on Windows',
        ipAddress: '192.168.1.1',
        userAgent: 'Mozilla/5.0',
    );
}

it('creates a refresh token with defaults', function (): void {
    $token = createRefreshToken();

    expect($token->tokenHash())->toBe(hash('sha256', 'token-value'))
        ->and($token->previousTokenHash())->toBe(hash('sha256', 'previous-token'))
        ->and($token->deviceName())->toBe('Chrome on Windows')
        ->and($token->ipAddress())->toBe('192.168.1.1')
        ->and($token->userAgent())->toBe('Mozilla/5.0')
        ->and($token->isExpired())->toBeFalse()
        ->and($token->isRevoked())->toBeFalse()
        ->and($token->lastUsedAt())->toBeNull()
        ->and($token->revokedAt())->toBeNull()
        ->and($token->revocationReason())->toBeNull();
});

it('revokes a token with a reason', function (): void {
    $token = createRefreshToken();

    $token->revoke('User logout');

    expect($token->isRevoked())->toBeTrue()
        ->and($token->revokedAt())->toBeInstanceOf(DateTimeImmutable::class)
        ->and($token->revocationReason())->toBe('User logout');
});

it('detects expired token', function (): void {
    $token = createRefreshToken((new DateTimeImmutable)->modify('-1 second'));

    expect($token->isExpired())->toBeTrue();
});

it('marks token as used', function (): void {
    $token = createRefreshToken();

    $token->markUsed();

    expect($token->lastUsedAt())->toBeInstanceOf(DateTimeImmutable::class);
});

it('exposes uuid identifiers', function (): void {
    $token = createRefreshToken();

    expect($token->id()->toString())->toMatch('/^[0-9a-f]{8}-[0-9a-f]{4}-7[0-9a-f]{3}-[89ab][0-9a-f]{3}-[0-9a-f]{12}$/i')
        ->and($token->userId()->toString())->toMatch('/^[0-9a-f]{8}-[0-9a-f]{4}-7[0-9a-f]{3}-[89ab][0-9a-f]{3}-[0-9a-f]{12}$/i')
        ->and($token->tokenFamily()->toString())->toMatch('/^[0-9a-f]{8}-[0-9a-f]{4}-7[0-9a-f]{3}-[89ab][0-9a-f]{3}-[0-9a-f]{12}$/i')
        ->and($token->sessionId())->toBeInstanceOf(SessionId::class);
});

it('exposes created at timestamp', function (): void {
    $token = createRefreshToken();

    expect($token->createdAt())->toBeInstanceOf(DateTimeImmutable::class);
});

it('exposes device fingerprint', function (): void {
    $token = createRefreshToken();

    expect($token->deviceFingerprint())->toBeInstanceOf(DeviceFingerprint::class);
});
