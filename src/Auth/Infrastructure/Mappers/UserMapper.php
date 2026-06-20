<?php

declare(strict_types=1);

namespace Urbania\Auth\Infrastructure\Mappers;

use App\Models\User as UserModel;
use Urbania\Auth\Domain\Entities\UserEntity;
use Urbania\Auth\Domain\ValueObjects\Password;
use Urbania\Auth\Domain\ValueObjects\UserRole;
use Urbania\Auth\Domain\ValueObjects\UserStatus;
use Urbania\Shared\Domain\ValueObjects\Email;
use Urbania\Shared\Domain\ValueObjects\Uuid;

final readonly class UserMapper
{
    public function toDomain(UserModel $model): UserEntity
    {
        $password = Password::fromHash($model->password_hash);
        $email = Email::fromString($model->email);
        $role = UserRole::from($model->role);

        $user = UserEntity::create(
            email: $email,
            name: $model->name,
            password: $password,
            role: $role,
        );

        $this->setPrivateProperty($user, 'id', Uuid::fromString($model->id));
        $this->setPrivateProperty($user, 'status', UserStatus::from($model->status));
        $this->setPrivateProperty($user, 'mfaEnabled', (bool) $model->mfa_enabled);
        $this->setPrivateProperty($user, 'mfaSecret', $model->mfa_secret);
        $this->setPrivateProperty($user, 'mfaBackupCodes', $model->mfa_backup_codes ?? []);
        $this->setPrivateProperty($user, 'emailVerifiedAt', $this->toDateTimeImmutable($model->email_verified_at));
        $this->setPrivateProperty($user, 'failedLoginAttempts', (int) $model->failed_login_attempts);
        $this->setPrivateProperty($user, 'lockedUntil', $this->toDateTimeImmutable($model->locked_until));
        $this->setPrivateProperty($user, 'lastLoginAt', $this->toDateTimeImmutable($model->last_login_at));
        $this->setPrivateProperty($user, 'lastLoginIp', $model->last_login_ip);
        $this->setPrivateProperty($user, 'passwordChangedAt', $this->toDateTimeImmutable($model->password_changed_at));
        $this->setPrivateProperty($user, 'mustChangePassword', (bool) $model->must_change_password);
        $this->setPrivateProperty($user, 'createdAt', $this->toDateTimeImmutable($model->created_at) ?? throw new \RuntimeException('Expected non-null datetime'));
        $this->setPrivateProperty($user, 'updatedAt', $this->toDateTimeImmutable($model->updated_at) ?? throw new \RuntimeException('Expected non-null datetime'));
        $this->setPrivateProperty($user, 'deletedAt', $this->toDateTimeImmutable($model->deleted_at));

        return $user;
    }

    /**
     * @return array<string, mixed>
     */
    public function toPersistence(UserEntity $entity): array
    {
        return [
            'id' => $entity->id()->toString(),
            'email' => $entity->email()->toString(),
            'name' => $entity->name(),
            'phone' => null,
            'unit' => null,
            'avatar_url' => null,
            'password_hash' => $entity->passwordHash()->toString(),
            'email_verified_at' => $entity->emailVerifiedAt()?->format('Y-m-d H:i:s'),
            'mfa_secret' => $entity->mfaSecret(),
            'mfa_enabled' => $entity->isMfaEnabled(),
            'mfa_backup_codes' => $entity->mfaBackupCodes(),
            'failed_login_attempts' => $entity->failedLoginAttempts(),
            'locked_until' => $entity->lockedUntil()?->format('Y-m-d H:i:s'),
            'last_login_at' => $entity->lastLoginAt()?->format('Y-m-d H:i:s'),
            'last_login_ip' => $entity->lastLoginIp(),
            'password_changed_at' => $entity->passwordChangedAt()?->format('Y-m-d H:i:s'),
            'must_change_password' => $entity->mustChangePassword(),
            'role' => $entity->role()->value,
            'status' => $entity->status()->value,
            'deleted_at' => $entity->deletedAt()?->format('Y-m-d H:i:s'),
        ];
    }

    private function setPrivateProperty(object $object, string $property, mixed $value): void
    {
        $reflection = new \ReflectionProperty($object, $property);
        $reflection->setAccessible(true);
        $reflection->setValue($object, $value);
    }

    private function toDateTimeImmutable(mixed $value): ?\DateTimeImmutable
    {
        if ($value === null) {
            return null;
        }

        assert($value instanceof \DateTime);

        return \DateTimeImmutable::createFromMutable($value);
    }
}
