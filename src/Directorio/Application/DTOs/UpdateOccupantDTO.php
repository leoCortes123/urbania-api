<?php

declare(strict_types=1);

namespace Directorio\Application\DTOs;

final readonly class UpdateOccupantDTO
{
    public function __construct(
        public ?string $occupantTypeId = null,
        public ?bool $isPrimary = null,
        public ?string $moveInDate = null,
        public ?string $moveOutDate = null,
        public ?bool $isActive = null,
    ) {}
}
