<?php

declare(strict_types=1);

namespace Urbania\Directorio\Application\DTOs;

final readonly class OccupantTypeResponseDTO
{
    public function __construct(
        public string $id,
        public string $code,
        public string $name,
        public ?string $description,
        public int $sortOrder,
        public bool $isActive,
        public int $occupantsCount = 0,
        public string $createdAt = '',
        public string $updatedAt = '',
    ) {}
}
