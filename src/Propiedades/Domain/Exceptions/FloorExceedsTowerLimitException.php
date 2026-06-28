<?php

declare(strict_types=1);

namespace Urbania\Propiedades\Domain\Exceptions;

use Urbania\Shared\Domain\Exceptions\DomainException;

final class FloorExceedsTowerLimitException extends DomainException
{
    public function __construct(string $message = 'El piso supera la cantidad de pisos de la torre')
    {
        parent::__construct('FLOOR_EXCEEDS_TOWER_LIMIT', $message, 422);
    }
}
