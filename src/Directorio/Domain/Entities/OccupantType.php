<?php

declare(strict_types=1);

namespace Directorio\Domain\Entities;

use Directorio\Domain\ValueObjects\OccupantTypeCode;

class OccupantType
{
    public function __construct(
        private readonly string $id,
        private readonly OccupantTypeCode $code,
        private readonly string $name,
        private readonly int $sortOrder,
        private readonly bool $isActive = true,
    ) {}

    public function id(): string
    {
        return $this->id;
    }

    public function code(): OccupantTypeCode
    {
        return $this->code;
    }

    public function name(): string
    {
        return $this->name;
    }

    public function sortOrder(): int
    {
        return $this->sortOrder;
    }

    public function isActive(): bool
    {
        return $this->isActive;
    }
}
