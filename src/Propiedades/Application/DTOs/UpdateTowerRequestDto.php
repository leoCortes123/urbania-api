<?php

declare(strict_types=1);

namespace Urbania\Propiedades\Application\DTOs;

final readonly class UpdateTowerRequestDto
{
    public function __construct(
        public ?string $name,
        public ?string $code,
        public ?int $floorCount,
        public ?bool $hasElevator,
        public ?string $description,
        public ?int $sortOrder,
    ) {}
}
