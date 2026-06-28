<?php

declare(strict_types=1);

namespace Urbania\Propiedades\Domain\Entities;

use Urbania\Shared\Domain\ValueObjects\Uuid;

final class PropertyEntity
{
    private function __construct(
        private Uuid $id,
        private Uuid $condominiumId,
        private Uuid $towerId,
        private Uuid $propertyTypeId,
        private Uuid $propertyStatusId,
        private int $floor,
        private string $unitNumber,
        private string $areaM2,
        private string $coefficient,
        private ?int $bedrooms,
        private ?int $bathrooms,
        private bool $hasParking,
        private ?string $parkingLot,
        private ?string $notes,
        private \DateTimeImmutable $createdAt,
        private \DateTimeImmutable $updatedAt,
        private ?\DateTimeImmutable $deletedAt,
    ) {}

    public static function create(
        Uuid $condominiumId,
        Uuid $towerId,
        Uuid $propertyTypeId,
        Uuid $propertyStatusId,
        int $floor,
        string $unitNumber,
        string $areaM2,
        string $coefficient,
        ?int $bedrooms = null,
        ?int $bathrooms = null,
        bool $hasParking = false,
        ?string $parkingLot = null,
        ?string $notes = null,
    ): self {
        $now = new \DateTimeImmutable;

        return new self(
            Uuid::v7(),
            $condominiumId,
            $towerId,
            $propertyTypeId,
            $propertyStatusId,
            $floor,
            $unitNumber,
            $areaM2,
            $coefficient,
            $bedrooms,
            $bathrooms,
            $hasParking,
            $parkingLot,
            $notes,
            $now,
            $now,
            null,
        );
    }

    public static function reconstitute(
        Uuid $id,
        Uuid $condominiumId,
        Uuid $towerId,
        Uuid $propertyTypeId,
        Uuid $propertyStatusId,
        int $floor,
        string $unitNumber,
        string $areaM2,
        string $coefficient,
        ?int $bedrooms,
        ?int $bathrooms,
        bool $hasParking,
        ?string $parkingLot,
        ?string $notes,
        \DateTimeImmutable $createdAt,
        \DateTimeImmutable $updatedAt,
        ?\DateTimeImmutable $deletedAt,
    ): self {
        return new self(
            $id,
            $condominiumId,
            $towerId,
            $propertyTypeId,
            $propertyStatusId,
            $floor,
            $unitNumber,
            $areaM2,
            $coefficient,
            $bedrooms,
            $bathrooms,
            $hasParking,
            $parkingLot,
            $notes,
            $createdAt,
            $updatedAt,
            $deletedAt,
        );
    }

    public function id(): Uuid
    {
        return $this->id;
    }

    public function condominiumId(): Uuid
    {
        return $this->condominiumId;
    }

    public function towerId(): Uuid
    {
        return $this->towerId;
    }

    public function propertyTypeId(): Uuid
    {
        return $this->propertyTypeId;
    }

    public function propertyStatusId(): Uuid
    {
        return $this->propertyStatusId;
    }

    public function floor(): int
    {
        return $this->floor;
    }

    public function unitNumber(): string
    {
        return $this->unitNumber;
    }

    public function areaM2(): string
    {
        return $this->areaM2;
    }

    public function coefficient(): string
    {
        return $this->coefficient;
    }

    public function bedrooms(): ?int
    {
        return $this->bedrooms;
    }

    public function bathrooms(): ?int
    {
        return $this->bathrooms;
    }

    public function hasParking(): bool
    {
        return $this->hasParking;
    }

    public function parkingLot(): ?string
    {
        return $this->parkingLot;
    }

    public function notes(): ?string
    {
        return $this->notes;
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

    public function update(
        ?Uuid $towerId = null,
        ?Uuid $propertyTypeId = null,
        ?int $floor = null,
        ?string $unitNumber = null,
        ?string $areaM2 = null,
        ?string $coefficient = null,
        ?int $bedrooms = null,
        ?int $bathrooms = null,
        ?bool $hasParking = null,
        ?string $parkingLot = null,
        ?string $notes = null,
    ): void {
        $this->towerId = $towerId ?? $this->towerId;
        $this->propertyTypeId = $propertyTypeId ?? $this->propertyTypeId;
        $this->floor = $floor ?? $this->floor;
        $this->unitNumber = $unitNumber ?? $this->unitNumber;
        $this->areaM2 = $areaM2 ?? $this->areaM2;
        $this->coefficient = $coefficient ?? $this->coefficient;
        $this->bedrooms = $bedrooms ?? $this->bedrooms;
        $this->bathrooms = $bathrooms ?? $this->bathrooms;
        $this->hasParking = $hasParking ?? $this->hasParking;
        $this->parkingLot = $parkingLot ?? $this->parkingLot;
        $this->notes = $notes ?? $this->notes;
        $this->updatedAt = new \DateTimeImmutable;
    }

    public function changeStatus(Uuid $propertyStatusId): void
    {
        $this->propertyStatusId = $propertyStatusId;
        $this->updatedAt = new \DateTimeImmutable;
    }
}
