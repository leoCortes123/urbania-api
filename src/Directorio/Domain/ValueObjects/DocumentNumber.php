<?php

declare(strict_types=1);

namespace Directorio\Domain\ValueObjects;

class DocumentNumber
{
    private string $value;

    public function __construct(string $value)
    {
        $normalized = preg_replace('/\s+/', '', $value);

        if ($normalized === null) {
            throw new \InvalidArgumentException('El número de documento no puede procesarse');
        }

        $sanitized = trim($normalized);

        if ($sanitized === '') {
            throw new \InvalidArgumentException('El número de documento no puede estar vacío');
        }

        $this->value = $sanitized;
    }

    public function value(): string
    {
        return $this->value;
    }

    public function equals(DocumentNumber $other): bool
    {
        return $this->value === $other->value;
    }

    public function __toString(): string
    {
        return $this->value;
    }
}
