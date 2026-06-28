<?php

declare(strict_types=1);

namespace Urbania\Propiedades\Domain\Entities;

use Urbania\Shared\Domain\ValueObjects\Uuid;

final class PropertyStatusLogEntry
{
    private function __construct(
        private Uuid $id,
        private Uuid $propertyId,
        private ?Uuid $fromStatusId,
        private Uuid $toStatusId,
        private Uuid $changedByUserId,
        private string $reason,
        private \DateTimeImmutable $createdAt,
    ) {}

    public static function create(
        Uuid $propertyId,
        ?Uuid $fromStatusId,
        Uuid $toStatusId,
        Uuid $changedByUserId,
        string $reason,
    ): self {
        return new self(
            Uuid::v7(),
            $propertyId,
            $fromStatusId,
            $toStatusId,
            $changedByUserId,
            $reason,
            new \DateTimeImmutable,
        );
    }

    public static function reconstitute(
        Uuid $id,
        Uuid $propertyId,
        ?Uuid $fromStatusId,
        Uuid $toStatusId,
        Uuid $changedByUserId,
        string $reason,
        \DateTimeImmutable $createdAt,
    ): self {
        return new self(
            $id,
            $propertyId,
            $fromStatusId,
            $toStatusId,
            $changedByUserId,
            $reason,
            $createdAt,
        );
    }

    public function id(): Uuid
    {
        return $this->id;
    }

    public function propertyId(): Uuid
    {
        return $this->propertyId;
    }

    public function fromStatusId(): ?Uuid
    {
        return $this->fromStatusId;
    }

    public function toStatusId(): Uuid
    {
        return $this->toStatusId;
    }

    public function changedByUserId(): Uuid
    {
        return $this->changedByUserId;
    }

    public function reason(): string
    {
        return $this->reason;
    }

    public function createdAt(): \DateTimeImmutable
    {
        return $this->createdAt;
    }
}
