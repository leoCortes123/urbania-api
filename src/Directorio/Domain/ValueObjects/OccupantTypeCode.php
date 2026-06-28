<?php

declare(strict_types=1);

namespace Directorio\Domain\ValueObjects;

class OccupantTypeCode
{
    private string $value;

    public function __construct(string $value)
    {
        $this->value = $value;
    }

    public function value(): string
    {
        return $this->value;
    }

    public function equals(OccupantTypeCode $other): bool
    {
        return $this->value === $other->value;
    }

    public function __toString(): string
    {
        return $this->value;
    }
}
