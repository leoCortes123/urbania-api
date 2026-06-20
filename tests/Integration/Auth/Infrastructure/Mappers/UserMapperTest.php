<?php

declare(strict_types=1);

use Database\Factories\UserFactory;
use Urbania\Auth\Domain\ValueObjects\Password;
use Urbania\Auth\Domain\ValueObjects\UserRole;
use Urbania\Auth\Domain\ValueObjects\UserStatus;
use Urbania\Auth\Infrastructure\Mappers\UserMapper;
use Urbania\Shared\Domain\ValueObjects\Email;
use Urbania\Shared\Domain\ValueObjects\Uuid;

beforeEach(function (): void {
    $this->mapper = new UserMapper;
});

it('converts an eloquent model to a user entity with all fields', function (): void {
    $model = UserFactory::new()
        ->locked()
        ->withMfa()
        ->mustChangePassword()
        ->admin()
        ->create();

    $entity = $this->mapper->toDomain($model);

    expect($entity->id()->toString())->toBe($model->id)
        ->and($entity->email()->toString())->toBe($model->email)
        ->and($entity->name())->toBe($model->name)
        ->and($entity->passwordHash())->toBeInstanceOf(Password::class)
        ->and($entity->passwordHash()->toString())->toBe($model->password_hash)
        ->and($entity->role())->toBe(UserRole::ADMIN)
        ->and($entity->status())->toBe(UserStatus::from($model->status))
        ->and($entity->isMfaEnabled())->toBeTrue()
        ->and($entity->mfaSecret())->toBe($model->mfa_secret)
        ->and($entity->mfaBackupCodes())->toBe($model->mfa_backup_codes)
        ->and($entity->failedLoginAttempts())->toBe($model->failed_login_attempts)
        ->and($entity->mustChangePassword())->toBeTrue()
        ->and($entity->lockedUntil()?->getTimestamp())->toBe($model->locked_until->getTimestamp());
});

it('converts a user entity to a persistence array with correct values', function (): void {
    $entity = UserFactory::new()->create();
    $domain = $this->mapper->toDomain($entity);

    $persistence = $this->mapper->toPersistence($domain);

    expect($persistence['id'])->toBe($entity->id)
        ->and($persistence['email'])->toBe($entity->email)
        ->and($persistence['name'])->toBe($entity->name)
        ->and($persistence['password_hash'])->toBe($entity->password_hash)
        ->and($persistence['role'])->toBe($entity->role)
        ->and($persistence['status'])->toBe($entity->status);
});

it('preserves all values through bidirectional mapping', function (): void {
    $model = UserFactory::new()
        ->locked()
        ->withMfa()
        ->mustChangePassword()
        ->admin()
        ->create();

    $entity = $this->mapper->toDomain($model);
    $persistence = $this->mapper->toPersistence($entity);

    expect($persistence['id'])->toBe($model->id)
        ->and($persistence['email'])->toBe($model->email)
        ->and($persistence['name'])->toBe($model->name)
        ->and($persistence['password_hash'])->toBe($model->password_hash)
        ->and($persistence['mfa_secret'])->toBe($model->mfa_secret)
        ->and($persistence['mfa_enabled'])->toBe($model->mfa_enabled)
        ->and($persistence['mfa_backup_codes'])->toBe($model->mfa_backup_codes)
        ->and($persistence['failed_login_attempts'])->toBe($model->failed_login_attempts)
        ->and($persistence['locked_until'])->toBe($model->locked_until->format('Y-m-d H:i:s'))
        ->and($persistence['must_change_password'])->toBe($model->must_change_password)
        ->and($persistence['role'])->toBe($model->role)
        ->and($persistence['status'])->toBe($model->status);
});

it('creates value objects correctly from raw values', function (): void {
    $entity = UserFactory::new()->create();
    $domain = $this->mapper->toDomain($entity);

    expect($domain->id())->toBeInstanceOf(Uuid::class)
        ->and($domain->email())->toBeInstanceOf(Email::class)
        ->and($domain->role())->toBeInstanceOf(UserRole::class)
        ->and($domain->status())->toBeInstanceOf(UserStatus::class)
        ->and($domain->passwordHash())->toBeInstanceOf(Password::class);
});

it('maps only changed fields in partial persistence', function (): void {
    $model = UserFactory::new()->create([
        'name' => 'Original Name',
        'phone' => '3001234567',
        'avatar_url' => 'https://urbania.example.com/old.jpg',
    ]);

    $domain = $this->mapper->toDomain($model);
    $domain->updateProfile('Updated Name', '3007654321', 'https://urbania.example.com/new.jpg');

    $partial = $this->mapper->toPersistencePartial($domain, $domain->changedFields());

    expect($partial)
        ->toHaveKey('name', 'Updated Name')
        ->toHaveKey('phone', '3007654321')
        ->toHaveKey('avatar_url', 'https://urbania.example.com/new.jpg')
        ->toHaveKey('updated_at')
        ->not->toHaveKey('email')
        ->not->toHaveKey('password_hash');
});
