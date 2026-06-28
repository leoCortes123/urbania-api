<?php

declare(strict_types=1);

namespace Directorio\Domain\ValueObjects;

class DocumentType
{
    private const ALLOWED_TYPES = ['CC', 'NIT', 'CE', 'Pasaporte', 'Otro'];

    private string $value;

    public function __construct(string $value)
    {
        if (! in_array($value, self::ALLOWED_TYPES, true)) {
            throw new \InvalidArgumentException(
                "Tipo de documento inválido: {$value}. Permitidos: ".implode(', ', self::ALLOWED_TYPES)
            );
        }
        $this->value = $value;
    }

    public function value(): string
    {
        return $this->value;
    }

    public function equals(DocumentType $other): bool
    {
        return $this->value === $other->value;
    }

    public function __toString(): string
    {
        return $this->value;
    }
}
