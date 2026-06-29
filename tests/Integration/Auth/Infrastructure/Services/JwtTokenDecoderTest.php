<?php

declare(strict_types=1);

use Illuminate\Support\Facades\Redis;
use Urbania\Auth\Domain\ValueObjects\SessionId;
use Urbania\Auth\Infrastructure\Services\JwtTokenDecoder;
use Urbania\Auth\Infrastructure\Services\PhpOpenSourceSaverJwtService;
use Urbania\Shared\Application\Services\TokenDecoderInterface;

beforeEach(function (): void {
    $this->jwtService = new PhpOpenSourceSaverJwtService;
    $this->decoder = new JwtTokenDecoder($this->jwtService);
    Redis::flushall();
});

afterEach(function (): void {
    Redis::flushall();
});

it('implements token decoder interface', function (): void {
    expect($this->decoder)->toBeInstanceOf(TokenDecoderInterface::class);
});

it('decodes a valid access token', function (): void {
    $token = $this->jwtService->generateAccessToken(
        userId: '018fffff-ffff-7fff-8fff-ffffffffffff',
        role: 'user',
        mfaVerified: false,
        sessionId: SessionId::generate(),
        deviceFingerprint: 'fp',
        organizationId: '01900000-0000-7fff-8fff-ffffffffffff',
    );

    $decoded = $this->decoder->decode($token->toString());

    expect($decoded)
        ->toHaveKey('sub')
        ->and($decoded['sub'])->toBe('018fffff-ffff-7fff-8fff-ffffffffffff')
        ->and($decoded)->toHaveKey('org_id')
        ->and($decoded['org_id'])->toBe('01900000-0000-7fff-8fff-ffffffffffff');
});

it('validates a token correctly', function (): void {
    $token = $this->jwtService->generateAccessToken(
        userId: '018fffff-ffff-7fff-8fff-ffffffffffff',
        role: 'user',
        mfaVerified: false,
        sessionId: SessionId::generate(),
        deviceFingerprint: 'fp',
        organizationId: '01900000-0000-7fff-8fff-ffffffffffff',
    );

    expect($this->decoder->validate($token->toString()))->toBeTrue()
        ->and($this->decoder->validate('invalid-token'))->toBeFalse();
});
