<?php

declare(strict_types=1);

use Urbania\Auth\Domain\ValueObjects\DeviceFingerprint;

it('calculates a fingerprint from device data', function (): void {
    $fp = DeviceFingerprint::calculate('Mozilla/5.0', '192.168.1.1', 'en-US', 'Chrome on Windows');

    expect($fp->toString())->toMatch('/^[a-f0-9]{64}$/i');
});

it('produces the same hash for same inputs', function (): void {
    $a = DeviceFingerprint::calculate('Mozilla/5.0', '192.168.1.1', 'en-US', 'Chrome');
    $b = DeviceFingerprint::calculate('Mozilla/5.0', '192.168.1.1', 'en-US', 'Chrome');

    expect($a->equals($b))->toBeTrue();
});

it('produces different hashes for different inputs', function (): void {
    $a = DeviceFingerprint::calculate('Mozilla/5.0', '192.168.1.1', 'en-US', 'Chrome');
    $b = DeviceFingerprint::calculate('Mozilla/5.0', '192.168.1.2', 'en-US', 'Chrome');

    expect($a->equals($b))->toBeFalse();
});

it('creates from a valid hash', function (): void {
    $hash = hash('sha256', 'raw');
    $fp = DeviceFingerprint::fromHash($hash);

    expect($fp->toString())->toBe($hash);
});

it('throws for invalid hash format', function (): void {
    DeviceFingerprint::fromHash('invalid');
})->throws(InvalidArgumentException::class, 'Invalid device fingerprint hash');

it('casts to string', function (): void {
    $fp = DeviceFingerprint::calculate('Mozilla/5.0', '192.168.1.1', 'en-US', 'Chrome');

    expect((string) $fp)->toBe($fp->toString());
});
