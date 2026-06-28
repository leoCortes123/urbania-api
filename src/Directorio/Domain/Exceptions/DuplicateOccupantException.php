<?php

declare(strict_types=1);

namespace Directorio\Domain\Exceptions;

class DuplicateOccupantException extends \DomainException
{
    public function __construct()
    {
        parent::__construct('La persona ya tiene este rol asignado en la misma unidad', 409);
    }
}
