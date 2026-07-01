<?php

declare(strict_types=1);

namespace Urbania\Comunicaciones\Domain\ValueObjects;

enum AnnouncementStatus: string
{
    case BORRADOR = 'borrador';
    case PROGRAMADO = 'programado';
    case ENVIADO = 'enviado';

    public static function fromString(string $value): self
    {
        return self::tryFrom($value) ?? throw new \InvalidArgumentException("Estado de comunicado inválido: {$value}");
    }
}
