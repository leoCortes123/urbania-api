<?php

declare(strict_types=1);

namespace Urbania\Propiedades\Application\DTOs;

use Urbania\Shared\Domain\ValueObjects\Uuid;

final readonly class CreateTowerRequestDto
{
    public function __construct(
        public Uuid $condominiumId,
        public string $name,
        public ?string $code,
        public int $floorCount,
        public bool $hasElevator,
        public ?string $description,
        public int $sortOrder,
    ) {}
}
