<?php

declare(strict_types=1);

use Urbania\Auth\Domain\Entities\UserEntity;
use Urbania\Auth\Domain\ValueObjects\Password;
use Urbania\Auth\Domain\ValueObjects\UserRole;
use Urbania\Auth\Domain\ValueObjects\UserStatus;
use Urbania\Shared\Domain\ValueObjects\Email;

function createUser(): UserEntity
{
    return UserEntity::create(
        Email::fromString('user@example.com'),
        'John Doe',
        Password::fromPlainText('SecureP@ss123'),
        UserRole::USER,
    );
}

it('creates a user with default values', function (): void {
    $user = createUser();

    expect($user->email()->toString())->toBe('user@example.com')
        ->and($user->name())->toBe('John Doe')
        ->and($user->role())->toBe(UserRole::USER)
        ->and($user->status())->toBe(UserStatus::ACTIVE)
        ->and($user->isMfaEnabled())->toBeFalse()
        ->and($user->failedLoginAttempts())->toBe(0)
        ->and($user->isLocked())->toBeFalse()
        ->and($user->mustChangePassword())->toBeFalse()
        ->and($user->emailVerifiedAt())->toBeNull()
        ->and($user->deletedAt())->toBeNull();
});

it('records failed login attempts and locks after five', function (): void {
    $user = createUser();

    $user->recordFailedLogin();
    $user->recordFailedLogin();
    $user->recordFailedLogin();
    $user->recordFailedLogin();

    expect($user->failedLoginAttempts())->toBe(4)
        ->and($user->isLocked())->toBeFalse();

    $user->recordFailedLogin();

    expect($user->failedLoginAttempts())->toBe(5)
        ->and($user->isLocked())->toBeTrue()
        ->and($user->lockedUntil())->toBeInstanceOf(DateTimeImmutable::class);
});

it('unlocks a locked user', function (): void {
    $user = createUser();
    $user->recordFailedLogin();
    $user->recordFailedLogin();
    $user->recordFailedLogin();
    $user->recordFailedLogin();
    $user->recordFailedLogin();

    expect($user->isLocked())->toBeTrue();

    $user->unlock();

    expect($user->isLocked())->toBeFalse()
        ->and($user->failedLoginAttempts())->toBe(0)
        ->and($user->lockedUntil())->toBeNull();
});

it('records successful login and resets attempts', function (): void {
    $user = createUser();
    $user->recordFailedLogin();
    $user->recordFailedLogin();

    $user->recordSuccessfulLogin('192.168.1.1');

    expect($user->failedLoginAttempts())->toBe(0)
        ->and($user->lastLoginIp())->toBe('192.168.1.1')
        ->and($user->lastLoginAt())->toBeInstanceOf(DateTimeImmutable::class)
        ->and($user->isLocked())->toBeFalse();
});

it('changes password and updates timestamp', function (): void {
    $user = createUser();
    $previousUpdatedAt = $user->updatedAt();

    sleep(1);
    $user->changePassword(Password::fromPlainText('NewSecureP@ss123'));

    expect($user->passwordHash()->verify('NewSecureP@ss123'))->toBeTrue()
        ->and($user->passwordChangedAt())->toBeInstanceOf(DateTimeImmutable::class)
        ->and($user->updatedAt())->toBeGreaterThan($previousUpdatedAt);
});

it('marks email as verified', function (): void {
    $user = createUser();

    $user->markEmailAsVerified();

    expect($user->emailVerifiedAt())->toBeInstanceOf(DateTimeImmutable::class);
});

it('suspends and activates user', function (): void {
    $user = createUser();

    $user->suspend();

    expect($user->status())->toBe(UserStatus::SUSPENDED);

    $user->activate();

    expect($user->status())->toBe(UserStatus::ACTIVE);
});

it('soft deletes user', function (): void {
    $user = createUser();

    $user->softDelete();

    expect($user->status())->toBe(UserStatus::INACTIVE)
        ->and($user->deletedAt())->toBeInstanceOf(DateTimeImmutable::class);
});

it('enables and disables mfa', function (): void {
    $user = createUser();

    $user->enableMfa('secret123', ['code1', 'code2']);

    expect($user->isMfaEnabled())->toBeTrue()
        ->and($user->mfaSecret())->toBe('secret123')
        ->and($user->mfaBackupCodes())->toBe(['code1', 'code2']);

    $user->disableMfa();

    expect($user->isMfaEnabled())->toBeFalse()
        ->and($user->mfaSecret())->toBeNull()
        ->and($user->mfaBackupCodes())->toBe([]);
});

it('exposes created and updated timestamps', function (): void {
    $user = createUser();

    expect($user->createdAt())->toBeInstanceOf(DateTimeImmutable::class)
        ->and($user->updatedAt())->toBeInstanceOf(DateTimeImmutable::class);
});

it('exposes id as uuid', function (): void {
    $user = createUser();

    expect($user->id()->toString())->toMatch('/^[0-9a-f]{8}-[0-9a-f]{4}-7[0-9a-f]{3}-[89ab][0-9a-f]{3}-[0-9a-f]{12}$/i');
});
