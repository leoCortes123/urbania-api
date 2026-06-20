<?php

declare(strict_types=1);

use Urbania\Shared\Domain\ValueObjects\Uuid;

it('generates a valid uuid v7', function (): void {
    $uuid = Uuid::v7();

    expect($uuid->toString())->toMatch('/^[0-9a-f]{8}-[0-9a-f]{4}-7[0-9a-f]{3}-[89ab][0-9a-f]{3}-[0-9a-f]{12}$/i');
});

it('creates a uuid from a valid string', function (): void {
    $uuidString = '018fffff-1234-7abc-8def-0123456789ab';
    $uuid = Uuid::fromString($uuidString);

    expect($uuid->toString())->toBe(strtolower($uuidString));
});

it('throws when creating from invalid string', function (): void {
    Uuid::fromString('not-a-uuid');
})->throws(InvalidArgumentException::class, 'Invalid UUID:');

it('compares equality correctly', function (): void {
    $uuidString = '018fffff-1234-7abc-8def-0123456789ab';
    $a = Uuid::fromString($uuidString);
    $b = Uuid::fromString($uuidString);
    $c = Uuid::v7();

    expect($a->equals($b))->toBeTrue()
        ->and($a->equals($c))->toBeFalse();
});

it('casts to string', function (): void {
    $uuid = Uuid::v7();

    expect((string) $uuid)->toBe($uuid->toString());
});
