<?php

declare(strict_types=1);

namespace Urbania\Propiedades\Application\DTOs;

use Urbania\Shared\Domain\ValueObjects\Uuid;

final readonly class CreatePropertyRequestDto
{
    public function __construct(
        public Uuid $towerId,
        public Uuid $propertyTypeId,
        public ?Uuid $propertyStatusId,
        public int $floor,
        public string $unitNumber,
        public string $areaM2,
        public string $coefficient,
        public ?int $bedrooms,
        public ?int $bathrooms,
        public bool $hasParking,
        public ?string $parkingLot,
        public ?string $notes,
    ) {}
}
