<?php

declare(strict_types=1);

namespace Directorio\Application\DTOs;

final readonly class CreateOccupantDTO
{
    public function __construct(
        public string $contactId,
        public string $occupantTypeId,
        public bool $isPrimary = false,
        public ?string $moveInDate = null,
        public ?string $moveOutDate = null,
    ) {}
}
