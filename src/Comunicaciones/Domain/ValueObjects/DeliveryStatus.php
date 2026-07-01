<?php

declare(strict_types=1);

namespace Urbania\Comunicaciones\Domain\ValueObjects;

enum DeliveryStatus: string
{
    case ENVIADO = 'enviado';
    case ENTREGADO = 'entregado';
    case LEIDO = 'leido';
    case FALLIDO = 'fallido';

    public static function fromString(string $value): self
    {
        return self::tryFrom($value) ?? throw new \InvalidArgumentException("Estado de entrega inválido: {$value}");
    }
}
