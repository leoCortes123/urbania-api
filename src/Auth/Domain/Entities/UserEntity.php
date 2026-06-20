<?php

declare(strict_types=1);

namespace Urbania\Auth\Domain\Entities;

use Urbania\Auth\Domain\ValueObjects\Password;
use Urbania\Auth\Domain\ValueObjects\UserRole;
use Urbania\Auth\Domain\ValueObjects\UserStatus;
use Urbania\Shared\Domain\ValueObjects\Email;
use Urbania\Shared\Domain\ValueObjects\Uuid;

final class UserEntity
{
    private Uuid $id;

    private Email $email;

    private string $name;

    private Password $passwordHash;

    private UserRole $role;

    private UserStatus $status;

    private bool $mfaEnabled;

    private ?string $mfaSecret;

    /** @var array<int, string> */
    private array $mfaBackupCodes;

    private ?\DateTimeImmutable $emailVerifiedAt;

    private int $failedLoginAttempts;

    private ?\DateTimeImmutable $lockedUntil;

    private ?\DateTimeImmutable $lastLoginAt;

    private ?string $lastLoginIp;

    private ?\DateTimeImmutable $passwordChangedAt;

    private bool $mustChangePassword;

    private \DateTimeImmutable $createdAt;

    private \DateTimeImmutable $updatedAt;

    private ?\DateTimeImmutable $deletedAt;

    /**
     * @param  array<int, string>  $mfaBackupCodes
     */
    private function __construct(
        Uuid $id,
        Email $email,
        string $name,
        Password $passwordHash,
        UserRole $role,
        UserStatus $status,
        bool $mfaEnabled,
        ?string $mfaSecret,
        array $mfaBackupCodes,
        ?\DateTimeImmutable $emailVerifiedAt,
        int $failedLoginAttempts,
        ?\DateTimeImmutable $lockedUntil,
        ?\DateTimeImmutable $lastLoginAt,
        ?string $lastLoginIp,
        ?\DateTimeImmutable $passwordChangedAt,
        bool $mustChangePassword,
        \DateTimeImmutable $createdAt,
        \DateTimeImmutable $updatedAt,
        ?\DateTimeImmutable $deletedAt,
    ) {
        $this->id = $id;
        $this->email = $email;
        $this->name = $name;
        $this->passwordHash = $passwordHash;
        $this->role = $role;
        $this->status = $status;
        $this->mfaEnabled = $mfaEnabled;
        $this->mfaSecret = $mfaSecret;
        $this->mfaBackupCodes = $mfaBackupCodes;
        $this->emailVerifiedAt = $emailVerifiedAt;
        $this->failedLoginAttempts = $failedLoginAttempts;
        $this->lockedUntil = $lockedUntil;
        $this->lastLoginAt = $lastLoginAt;
        $this->lastLoginIp = $lastLoginIp;
        $this->passwordChangedAt = $passwordChangedAt;
        $this->mustChangePassword = $mustChangePassword;
        $this->createdAt = $createdAt;
        $this->updatedAt = $updatedAt;
        $this->deletedAt = $deletedAt;
    }

    public static function create(
        Email $email,
        string $name,
        Password $password,
        UserRole $role,
    ): self {
        $now = new \DateTimeImmutable;

        return new self(
            Uuid::v7(),
            $email,
            $name,
            $password,
            $role,
            UserStatus::ACTIVE,
            false,
            null,
            [],
            null,
            0,
            null,
            null,
            null,
            null,
            false,
            $now,
            $now,
            null,
        );
    }

    public function recordFailedLogin(): void
    {
        $this->failedLoginAttempts++;

        if ($this->failedLoginAttempts >= 5) {
            $this->lockedUntil = (new \DateTimeImmutable)->modify('+15 minutes');
        }

        $this->updatedAt = new \DateTimeImmutable;
    }

    public function recordSuccessfulLogin(string $ip): void
    {
        $this->failedLoginAttempts = 0;
        $this->lockedUntil = null;
        $this->lastLoginAt = new \DateTimeImmutable;
        $this->lastLoginIp = $ip;
        $this->updatedAt = new \DateTimeImmutable;
    }

    public function isLocked(): bool
    {
        return $this->lockedUntil !== null && $this->lockedUntil > new \DateTimeImmutable;
    }

    public function unlock(): void
    {
        $this->failedLoginAttempts = 0;
        $this->lockedUntil = null;
        $this->updatedAt = new \DateTimeImmutable;
    }

    public function changePassword(Password $password): void
    {
        $this->passwordHash = $password;
        $this->passwordChangedAt = new \DateTimeImmutable;
        $this->mustChangePassword = false;
        $this->updatedAt = new \DateTimeImmutable;
    }

    public function markEmailAsVerified(): void
    {
        $this->emailVerifiedAt = new \DateTimeImmutable;
        $this->updatedAt = new \DateTimeImmutable;
    }

    public function suspend(): void
    {
        $this->status = UserStatus::SUSPENDED;
        $this->updatedAt = new \DateTimeImmutable;
    }

    public function activate(): void
    {
        $this->status = UserStatus::ACTIVE;
        $this->updatedAt = new \DateTimeImmutable;
    }

    public function softDelete(): void
    {
        $this->status = UserStatus::INACTIVE;
        $this->deletedAt = new \DateTimeImmutable;
        $this->updatedAt = new \DateTimeImmutable;
    }

    /**
     * @param  array<int, string>  $backupCodes
     */
    public function enableMfa(string $secret, array $backupCodes): void
    {
        $this->mfaEnabled = true;
        $this->mfaSecret = $secret;
        $this->mfaBackupCodes = $backupCodes;
        $this->updatedAt = new \DateTimeImmutable;
    }

    public function disableMfa(): void
    {
        $this->mfaEnabled = false;
        $this->mfaSecret = null;
        $this->mfaBackupCodes = [];
        $this->updatedAt = new \DateTimeImmutable;
    }

    public function id(): Uuid
    {
        return $this->id;
    }

    public function email(): Email
    {
        return $this->email;
    }

    public function name(): string
    {
        return $this->name;
    }

    public function passwordHash(): Password
    {
        return $this->passwordHash;
    }

    public function role(): UserRole
    {
        return $this->role;
    }

    public function status(): UserStatus
    {
        return $this->status;
    }

    public function isMfaEnabled(): bool
    {
        return $this->mfaEnabled;
    }

    public function mfaSecret(): ?string
    {
        return $this->mfaSecret;
    }

    /**
     * @return array<int, string>
     */
    public function mfaBackupCodes(): array
    {
        return $this->mfaBackupCodes;
    }

    public function emailVerifiedAt(): ?\DateTimeImmutable
    {
        return $this->emailVerifiedAt;
    }

    public function failedLoginAttempts(): int
    {
        return $this->failedLoginAttempts;
    }

    public function lockedUntil(): ?\DateTimeImmutable
    {
        return $this->lockedUntil;
    }

    public function lastLoginAt(): ?\DateTimeImmutable
    {
        return $this->lastLoginAt;
    }

    public function lastLoginIp(): ?string
    {
        return $this->lastLoginIp;
    }

    public function passwordChangedAt(): ?\DateTimeImmutable
    {
        return $this->passwordChangedAt;
    }

    public function mustChangePassword(): bool
    {
        return $this->mustChangePassword;
    }

    public function createdAt(): \DateTimeImmutable
    {
        return $this->createdAt;
    }

    public function updatedAt(): \DateTimeImmutable
    {
        return $this->updatedAt;
    }

    public function deletedAt(): ?\DateTimeImmutable
    {
        return $this->deletedAt;
    }
}
