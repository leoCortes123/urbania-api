<?php

declare(strict_types=1);

namespace Urbania\Comunicaciones\Domain\ValueObjects;

enum SurveyType: string
{
    case SIMPLE = 'simple';
    case MULTIPLE = 'multiple';

    public static function fromString(string $value): self
    {
        return self::tryFrom($value) ?? throw new \InvalidArgumentException("Tipo de encuesta inválido: {$value}");
    }
}
