<?php

declare(strict_types=1);

use App\Models\User as UserModel;
use Database\Factories\UserFactory;
use Urbania\Auth\Domain\Entities\UserEntity;
use Urbania\Auth\Domain\Repositories\UserRepositoryInterface;
use Urbania\Auth\Domain\ValueObjects\Password;
use Urbania\Auth\Domain\ValueObjects\UserRole;
use Urbania\Auth\Infrastructure\Mappers\UserMapper;
use Urbania\Auth\Infrastructure\Persistence\EloquentUserRepository;
use Urbania\Shared\Domain\ValueObjects\Email;
use Urbania\Shared\Domain\ValueObjects\Uuid;

beforeEach(function (): void {
    $this->repository = new EloquentUserRepository(new UserMapper);
});

it('finds an existing user by email', function (): void {
    $model = UserFactory::new()->create();

    $found = $this->repository->findByEmail(Email::fromString($model->email));

    expect($found)->toBeInstanceOf(UserEntity::class)
        ->and($found->id()->toString())->toBe($model->id)
        ->and($found->email()->toString())->toBe($model->email);
});

it('returns null when email does not exist', function (): void {
    $result = $this->repository->findByEmail(Email::fromString('missing@example.com'));

    expect($result)->toBeNull();
});

it('finds an existing user by id', function (): void {
    $model = UserFactory::new()->create();

    $found = $this->repository->findById(Uuid::fromString($model->id));

    expect($found)->toBeInstanceOf(UserEntity::class)
        ->and($found->id()->toString())->toBe($model->id);
});

it('persists a new user with all fields', function (): void {
    $entity = UserEntity::create(
        email: Email::fromString('new@example.com'),
        name: 'New User',
        password: Password::fromPlainText('Password123!'),
        role: UserRole::USER,
    );

    $this->repository->save($entity);

    $model = UserModel::find($entity->id()->toString());

    expect($model)->not->toBeNull()
        ->and($model->email)->toBe('new@example.com')
        ->and($model->name)->toBe('New User')
        ->and($model->role)->toBe('user')
        ->and($model->status)->toBe('active')
        ->and(Uuid::fromString($model->id))->toBeInstanceOf(Uuid::class)
        ->and(preg_match('/^[0-9a-f]{8}-[0-9a-f]{4}-7[0-9a-f]{3}-[89ab][0-9a-f]{3}-[0-9a-f]{12}$/i', $model->id))->toBe(1);
});

it('updates an existing user', function (): void {
    $model = UserFactory::new()->create();
    $entity = $this->repository->findById(Uuid::fromString($model->id));

    $entity->recordFailedLogin();
    $this->repository->update($entity);

    $refreshed = UserModel::find($model->id);

    expect($refreshed->failed_login_attempts)->toBe(1)
        ->and($refreshed->updated_at->greaterThanOrEqualTo($model->updated_at))->toBeTrue();
});

it('deletes a user', function (): void {
    $model = UserFactory::new()->create();

    $this->repository->delete(Uuid::fromString($model->id));

    expect(UserModel::withTrashed()->find($model->id))->not->toBeNull()
        ->and(UserModel::find($model->id))->toBeNull()
        ->and($this->repository->findById(Uuid::fromString($model->id)))->toBeNull();
});

it('checks email existence correctly', function (): void {
    $model = UserFactory::new()->create();

    expect($this->repository->existsByEmail(Email::fromString($model->email)))->toBeTrue()
        ->and($this->repository->existsByEmail(Email::fromString('other@example.com')))->toBeFalse();
});

it('preserves all fields through save and find cycle', function (): void {
    $original = UserFactory::new()
        ->locked()
        ->withMfa()
        ->mustChangePassword()
        ->admin()
        ->create();

    $entity = $this->repository->findById(Uuid::fromString($original->id));

    expect($entity->id()->toString())->toBe($original->id)
        ->and($entity->email()->toString())->toBe($original->email)
        ->and($entity->name())->toBe($original->name)
        ->and($entity->role()->value)->toBe($original->role)
        ->and($entity->status()->value)->toBe($original->status)
        ->and($entity->isMfaEnabled())->toBe($original->mfa_enabled)
        ->and($entity->mfaSecret())->toBe($original->mfa_secret)
        ->and($entity->mfaBackupCodes())->toBe($original->mfa_backup_codes)
        ->and($entity->failedLoginAttempts())->toBe($original->failed_login_attempts)
        ->and($entity->mustChangePassword())->toBe($original->must_change_password)
        ->and($entity->lockedUntil()?->getTimestamp())->toBe($original->locked_until->getTimestamp());
});

it('resolves repository from container', function (): void {
    expect(app(UserRepositoryInterface::class))->toBeInstanceOf(EloquentUserRepository::class);
});
