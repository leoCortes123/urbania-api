<?php

declare(strict_types=1);

namespace Urbania\Directorio\Application\DTOs;

final readonly class OccupantResponseDTO
{
    public function __construct(
        public string $id,
        public string $propertyId,
        public string $contactId,
        public string $contactName,
        public string $occupantTypeId,
        public string $occupantTypeCode,
        public string $occupantTypeName,
        public bool $isPrimary,
        public ?string $moveInDate,
        public ?string $moveOutDate,
        public bool $isActive,
        public string $createdAt = '',
        public string $updatedAt = '',
    ) {}
}
