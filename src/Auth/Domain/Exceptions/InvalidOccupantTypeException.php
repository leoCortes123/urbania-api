<?php

declare(strict_types=1);

namespace Urbania\Auth\Domain\Exceptions;

use Urbania\Shared\Domain\Exceptions\DomainException;

final class InvalidOccupantTypeException extends DomainException
{
    public function __construct(string $message = 'El tipo de ocupante no es válido o no existe.')
    {
        parent::__construct('INVALID_OCCUPANT_TYPE', $message, 422);
    }
}
