<?php

declare(strict_types=1);

namespace Urbania\Comunicaciones\Domain\ValueObjects;

enum DeliveryChannel: string
{
    case WHATSAPP = 'whatsapp';
    case EMAIL = 'email';
    case PUSH = 'push';

    public static function fromString(string $value): self
    {
        return self::tryFrom($value) ?? throw new \InvalidArgumentException("Canal de entrega inválido: {$value}");
    }
}
