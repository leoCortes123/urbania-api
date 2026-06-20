<?php

declare(strict_types=1);

use Urbania\Auth\Domain\Exceptions\InvalidCredentialsException;
use Urbania\Auth\Domain\ValueObjects\Password;

it('hashes a valid plain text password', function (): void {
    $password = Password::fromPlainText('SecureP@ss123');

    expect($password->toString())->toStartWith('$argon2id$')
        ->and($password->verify('SecureP@ss123'))->toBeTrue()
        ->and($password->verify('wrong-password'))->toBeFalse();
});

it('throws when password is too short', function (): void {
    Password::fromPlainText('short');
})->throws(InvalidCredentialsException::class, 'Password must be at least 8 characters');

it('creates password from existing hash', function (): void {
    $plain = 'SecureP@ss123';
    $hash = Password::fromPlainText($plain)->toString();
    $password = Password::fromHash($hash);

    expect($password->toString())->toBe($hash)
        ->and($password->verify($plain))->toBeTrue();
});

it('detects rehash needs for old algorithm hash', function (): void {
    $oldHash = password_hash('SecureP@ss123', PASSWORD_ARGON2ID, [
        'memory_cost' => 1024,
        'time_cost' => 2,
        'threads' => 1,
    ]);
    $password = Password::fromHash($oldHash);

    expect($password->needsRehash())->toBeTrue();
});

it('does not need rehash for current settings', function (): void {
    $password = Password::fromPlainText('SecureP@ss123');

    expect($password->needsRehash())->toBeFalse();
});
