<?php

declare(strict_types=1);

use Urbania\Auth\Domain\ValueObjects\SessionId;
use Urbania\Shared\Domain\ValueObjects\Uuid;

it('generates a new session id', function (): void {
    $sessionId = SessionId::generate();

    expect($sessionId->toString())->toMatch('/^[0-9a-f]{8}-[0-9a-f]{4}-7[0-9a-f]{3}-[89ab][0-9a-f]{3}-[0-9a-f]{12}$/i');
});

it('creates from a valid uuid string', function (): void {
    $uuidString = '018fffff-1234-7abc-8def-0123456789ab';
    $sessionId = SessionId::fromString($uuidString);

    expect($sessionId->toString())->toBe(strtolower($uuidString));
});

it('creates from a uuid object', function (): void {
    $uuid = Uuid::v7();
    $sessionId = SessionId::fromUuid($uuid);

    expect($sessionId->toString())->toBe($uuid->toString());
});

it('compares equality correctly', function (): void {
    $uuid = Uuid::v7();
    $a = SessionId::fromUuid($uuid);
    $b = SessionId::fromUuid($uuid);
    $c = SessionId::generate();

    expect($a->equals($b))->toBeTrue()
        ->and($a->equals($c))->toBeFalse();
});

it('casts to string', function (): void {
    $sessionId = SessionId::generate();

    expect((string) $sessionId)->toBe($sessionId->toString());
});
