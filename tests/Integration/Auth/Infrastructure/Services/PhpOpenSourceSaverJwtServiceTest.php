<?php

declare(strict_types=1);

use Illuminate\Support\Facades\Redis;
use Urbania\Auth\Domain\ValueObjects\SessionId;
use Urbania\Auth\Infrastructure\Services\PhpOpenSourceSaverJwtService;

beforeEach(function (): void {
    $this->service = new PhpOpenSourceSaverJwtService;
    Redis::flushall();
});

afterEach(function (): void {
    Redis::flushall();
});

it('generates access token with all required claims', function (): void {
    $sessionId = SessionId::generate();

    $token = $this->service->generateAccessToken(
        userId: '018fffff-ffff-7fff-8fff-ffffffffffff',
        role: 'user',
        mfaVerified: true,
        sessionId: $sessionId,
        deviceFingerprint: 'device-fp-hash',
        organizationId: '01900000-0000-7fff-8fff-ffffffffffff',
    );

    $decoded = $this->service->decode($token->toString());

    expect($decoded)->toHaveKeys([
        'jti', 'sub', 'iss', 'aud', 'iat', 'nbf', 'exp',
        'role', 'mfa_verified', 'session_id', 'device_fp', 'org_id',
    ])
        ->and($decoded['iss'])->toBe('https://api.urbania.com')
        ->and($decoded['aud'])->toBe(['api.urbania.com', 'web.urbania.com', 'app.urbania'])
        ->and($decoded['sub'])->toBe('018fffff-ffff-7fff-8fff-ffffffffffff')
        ->and($decoded['role'])->toBe('user')
        ->and($decoded['mfa_verified'])->toBeTrue()
        ->and($decoded['session_id'])->toBe($sessionId->toString())
        ->and($decoded['device_fp'])->toBe('device-fp-hash')
        ->and($decoded['org_id'])->toBe('01900000-0000-7fff-8fff-ffffffffffff');
});

it('includes scope claim when provided', function (): void {
    $token = $this->service->generateAccessToken(
        userId: '018fffff-ffff-7fff-8fff-ffffffffffff',
        role: 'user',
        mfaVerified: false,
        sessionId: SessionId::generate(),
        deviceFingerprint: 'fp',
        organizationId: '01900000-0000-7fff-8fff-ffffffffffff',
        scope: 'change-password',
    );

    $decoded = $this->service->decode($token->toString());

    expect($decoded)->toHaveKey('scope')
        ->and($decoded['scope'])->toBe('change-password');
});

it('sets correct expiration for custom ttl', function (): void {
    $before = new DateTimeImmutable;

    $token = $this->service->generateAccessToken(
        userId: '018fffff-ffff-7fff-8fff-ffffffffffff',
        role: 'user',
        mfaVerified: false,
        sessionId: SessionId::generate(),
        deviceFingerprint: 'fp',
        organizationId: '01900000-0000-7fff-8fff-ffffffffffff',
        ttl: 60,
    );

    $after = new DateTimeImmutable;
    $decoded = $this->service->decode($token->toString());

    expect($decoded['exp'])->toBeGreaterThanOrEqual($before->getTimestamp() + 60)
        ->and($decoded['exp'])->toBeLessThanOrEqual($after->getTimestamp() + 70);
});

it('generates refresh token as 128 character hexadecimal string', function (): void {
    $refreshToken = $this->service->generateRefreshToken();

    expect($refreshToken)->toMatch('/^[a-f0-9]{128}$/i')
        ->and(strlen($refreshToken))->toBe(128);
});

it('decodes token payload correctly', function (): void {
    $token = $this->service->generateAccessToken(
        userId: '018fffff-ffff-7fff-8fff-ffffffffffff',
        role: 'admin',
        mfaVerified: false,
        sessionId: SessionId::generate(),
        deviceFingerprint: 'fp',
        organizationId: '01900000-0000-7fff-8fff-ffffffffffff',
    );

    $decoded = $this->service->decode($token->toString());

    expect($decoded['role'])->toBe('admin')
        ->and($decoded['sub'])->toBe('018fffff-ffff-7fff-8fff-ffffffffffff')
        ->and($decoded['org_id'])->toBe('01900000-0000-7fff-8fff-ffffffffffff');
});

it('validates a valid token and rejects expired token', function (): void {
    $validToken = $this->service->generateAccessToken(
        userId: '018fffff-ffff-7fff-8fff-ffffffffffff',
        role: 'user',
        mfaVerified: false,
        sessionId: SessionId::generate(),
        deviceFingerprint: 'fp',
        organizationId: '01900000-0000-7fff-8fff-ffffffffffff',
    );

    expect($this->service->validate($validToken->toString()))->toBeTrue();

    $expiredToken = $this->service->generateAccessToken(
        userId: '018fffff-ffff-7fff-8fff-ffffffffffff',
        role: 'user',
        mfaVerified: false,
        sessionId: SessionId::generate(),
        deviceFingerprint: 'fp',
        organizationId: '01900000-0000-7fff-8fff-ffffffffffff',
        ttl: -100,
    );

    expect($this->service->validate($expiredToken->toString()))->toBeFalse();
});

it('revokes token and detects it as blacklisted', function (): void {
    $token = $this->service->generateAccessToken(
        userId: '018fffff-ffff-7fff-8fff-ffffffffffff',
        role: 'user',
        mfaVerified: false,
        sessionId: SessionId::generate(),
        deviceFingerprint: 'fp',
        organizationId: '01900000-0000-7fff-8fff-ffffffffffff',
    );

    $decoded = $this->service->decode($token->toString());
    $jti = (string) $decoded['jti'];

    expect($this->service->isBlacklisted($jti))->toBeFalse();
    expect($this->service->validate($token->toString()))->toBeTrue();

    $this->service->revoke($jti);

    expect($this->service->isBlacklisted($jti))->toBeTrue();
    expect($this->service->validate($token->toString()))->toBeFalse();
});

it('rejects token with invalid signature', function (): void {
    $token = $this->service->generateAccessToken(
        userId: '018fffff-ffff-7fff-8fff-ffffffffffff',
        role: 'user',
        mfaVerified: false,
        sessionId: SessionId::generate(),
        deviceFingerprint: 'fp',
        organizationId: '01900000-0000-7fff-8fff-ffffffffffff',
    );

    $tamperedToken = $token->toString().'tampered';

    expect($this->service->validate($tamperedToken))->toBeFalse();
});
