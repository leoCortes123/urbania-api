<?php

declare(strict_types=1);

namespace Urbania\Comunicaciones\Domain\ValueObjects;

enum Segment: string
{
    case TODOS = 'todos';
    case TORRE = 'torre';
    case MOROSOS = 'morosos';
    case UNIDAD = 'unidad';

    public static function fromString(string $value): self
    {
        return self::tryFrom($value) ?? throw new \InvalidArgumentException("Segmento inválido: {$value}");
    }
}
