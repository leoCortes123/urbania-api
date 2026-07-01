<?php

declare(strict_types=1);

namespace Directorio\Domain\Exceptions;

use Urbania\Shared\Domain\Exceptions\DomainException;

final class DuplicateOccupantException extends DomainException
{
    public function __construct(string $message = 'La persona ya tiene este rol asignado en la misma unidad')
    {
        parent::__construct('DUPLICATE_OCCUPANT', $message, 409);
    }
}
