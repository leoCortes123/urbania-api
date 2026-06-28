<?php

declare(strict_types=1);

namespace Directorio\Domain\Entities;

class PropertyOccupant
{
    public function __construct(
        private readonly string $id,
        private readonly string $propertyId,
        private readonly string $contactId,
        private readonly string $occupantTypeId,
        private readonly bool $isPrimary = false,
        private readonly ?string $moveInDate = null,
        private readonly ?string $moveOutDate = null,
        private readonly bool $isActive = true,
        private readonly ?string $createdAt = null,
        private readonly ?string $updatedAt = null,
        private readonly ?string $deletedAt = null,
    ) {
        if ($moveInDate !== null && $moveOutDate !== null && $moveOutDate < $moveInDate) {
            throw new \InvalidArgumentException('La fecha de salida no puede ser anterior a la fecha de ingreso');
        }
    }

    public function id(): string
    {
        return $this->id;
    }

    public function propertyId(): string
    {
        return $this->propertyId;
    }

    public function contactId(): string
    {
        return $this->contactId;
    }

    public function occupantTypeId(): string
    {
        return $this->occupantTypeId;
    }

    public function isPrimary(): bool
    {
        return $this->isPrimary;
    }

    public function moveInDate(): ?string
    {
        return $this->moveInDate;
    }

    public function moveOutDate(): ?string
    {
        return $this->moveOutDate;
    }

    public function isActive(): bool
    {
        return $this->isActive;
    }

    public function createdAt(): ?string
    {
        return $this->createdAt;
    }

    public function updatedAt(): ?string
    {
        return $this->updatedAt;
    }

    public function deletedAt(): ?string
    {
        return $this->deletedAt;
    }

    public function isDeleted(): bool
    {
        return $this->deletedAt !== null;
    }

    public function isCurrentlyOccupying(): bool
    {
        return $this->isActive && ! $this->isDeleted();
    }
}
