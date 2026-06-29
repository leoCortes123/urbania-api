<?php

declare(strict_types=1);

namespace Urbania\Authorization\Domain\Entities;

use Urbania\Shared\Domain\ValueObjects\Uuid;

final readonly class RoleAssignment
{
    public function __construct(
        private Uuid $id,
        private Uuid $userId,
        private Uuid $roleId,
        private string $scopeType,
        private Uuid $scopeId,
        private ?\DateTimeImmutable $startsAt = null,
        private ?\DateTimeImmutable $endsAt = null,
    ) {}

    public function id(): Uuid
    {
        return $this->id;
    }

    public function userId(): Uuid
    {
        return $this->userId;
    }

    public function roleId(): Uuid
    {
        return $this->roleId;
    }

    public function scopeType(): string
    {
        return $this->scopeType;
    }

    public function scopeId(): Uuid
    {
        return $this->scopeId;
    }

    public function startsAt(): ?\DateTimeImmutable
    {
        return $this->startsAt;
    }

    public function endsAt(): ?\DateTimeImmutable
    {
        return $this->endsAt;
    }

    public function isActive(\DateTimeImmutable $now = new \DateTimeImmutable): bool
    {
        if ($this->startsAt !== null && $this->startsAt > $now) {
            return false;
        }

        if ($this->endsAt !== null && $this->endsAt < $now) {
            return false;
        }

        return true;
    }
}
