<?php

declare(strict_types=1);

namespace Urbania\Propiedades\Application\DTOs;

use Urbania\Propiedades\Domain\Entities\PropertyEntity;

final readonly class PropertyResponseDto
{
    /**
     * @param  array<string, mixed>|null  $tower
     * @param  array<string, mixed>|null  $type
     * @param  array<string, mixed>|null  $status
     */
    public function __construct(
        public string $id,
        public string $condominiumId,
        public string $towerId,
        public string $propertyTypeId,
        public string $propertyStatusId,
        public int $floor,
        public string $unitNumber,
        public string $areaM2,
        public string $coefficient,
        public ?int $bedrooms,
        public ?int $bathrooms,
        public bool $hasParking,
        public ?string $parkingLot,
        public ?string $notes,
        public ?array $tower,
        public ?array $type,
        public ?array $status,
        public ?string $fullDesignation,
        public int $residentsCount,
        public int $documentsCount,
        public string $createdAt,
        public string $updatedAt,
    ) {}

    public static function fromEntity(PropertyEntity $entity): self
    {
        return new self(
            id: $entity->id()->toString(),
            condominiumId: $entity->condominiumId()->toString(),
            towerId: $entity->towerId()->toString(),
            propertyTypeId: $entity->propertyTypeId()->toString(),
            propertyStatusId: $entity->propertyStatusId()->toString(),
            floor: $entity->floor(),
            unitNumber: $entity->unitNumber(),
            areaM2: $entity->areaM2(),
            coefficient: $entity->coefficient(),
            bedrooms: $entity->bedrooms(),
            bathrooms: $entity->bathrooms(),
            hasParking: $entity->hasParking(),
            parkingLot: $entity->parkingLot(),
            notes: $entity->notes(),
            tower: null,
            type: null,
            status: null,
            fullDesignation: null,
            residentsCount: 0,
            documentsCount: 0,
            createdAt: $entity->createdAt()->format('c'),
            updatedAt: $entity->updatedAt()->format('c'),
        );
    }
}
