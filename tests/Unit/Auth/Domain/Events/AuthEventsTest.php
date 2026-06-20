<?php

declare(strict_types=1);

use Urbania\Auth\Domain\Events\MfaDisabled;
use Urbania\Auth\Domain\Events\MfaEnabled;
use Urbania\Auth\Domain\Events\PasswordChanged;
use Urbania\Auth\Domain\Events\SuspiciousActivityDetected;
use Urbania\Auth\Domain\Events\UserLoggedIn;
use Urbania\Auth\Domain\Events\UserLoggedOut;
use Urbania\Auth\Domain\Events\UserRegistered;

it('user logged in event holds data', function (): void {
    $timestamp = new DateTimeImmutable;
    $event = new UserLoggedIn('user-id', '192.168.1.1', 'fp-hash', true, $timestamp);

    expect($event->userId)->toBe('user-id')
        ->and($event->ip)->toBe('192.168.1.1')
        ->and($event->deviceFp)->toBe('fp-hash')
        ->and($event->mfaUsed)->toBeTrue()
        ->and($event->timestamp)->toBe($timestamp);
});

it('user registered event holds data', function (): void {
    $timestamp = new DateTimeImmutable;
    $event = new UserRegistered('user-id', 'user@example.com', $timestamp);

    expect($event->userId)->toBe('user-id')
        ->and($event->email)->toBe('user@example.com')
        ->and($event->timestamp)->toBe($timestamp);
});

it('user logged out event holds data', function (): void {
    $timestamp = new DateTimeImmutable;
    $event = new UserLoggedOut('user-id', 'session-id', $timestamp);

    expect($event->userId)->toBe('user-id')
        ->and($event->sessionId)->toBe('session-id')
        ->and($event->timestamp)->toBe($timestamp);
});

it('password changed event holds data', function (): void {
    $timestamp = new DateTimeImmutable;
    $event = new PasswordChanged('user-id', '192.168.1.1', $timestamp);

    expect($event->userId)->toBe('user-id')
        ->and($event->ip)->toBe('192.168.1.1')
        ->and($event->timestamp)->toBe($timestamp);
});

it('mfa enabled event holds data', function (): void {
    $timestamp = new DateTimeImmutable;
    $event = new MfaEnabled('user-id', '192.168.1.1', $timestamp);

    expect($event->userId)->toBe('user-id')
        ->and($event->ip)->toBe('192.168.1.1')
        ->and($event->timestamp)->toBe($timestamp);
});

it('mfa disabled event holds data', function (): void {
    $timestamp = new DateTimeImmutable;
    $event = new MfaDisabled('user-id', '192.168.1.1', $timestamp);

    expect($event->userId)->toBe('user-id')
        ->and($event->ip)->toBe('192.168.1.1')
        ->and($event->timestamp)->toBe($timestamp);
});

it('suspicious activity detected event holds data', function (): void {
    $timestamp = new DateTimeImmutable;
    $event = new SuspiciousActivityDetected('user-id', 'refresh_reuse', '192.168.1.1', ['token' => 'abc'], $timestamp);

    expect($event->userId)->toBe('user-id')
        ->and($event->activityType)->toBe('refresh_reuse')
        ->and($event->ip)->toBe('192.168.1.1')
        ->and($event->details)->toBe(['token' => 'abc'])
        ->and($event->timestamp)->toBe($timestamp);
});
