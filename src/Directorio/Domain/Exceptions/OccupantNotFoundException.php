<?php

declare(strict_types=1);

namespace Directorio\Domain\Exceptions;

use Urbania\Shared\Domain\Exceptions\DomainException;

final class OccupantNotFoundException extends DomainException
{
    public function __construct(string $message = 'Vínculo de ocupante no encontrado')
    {
        parent::__construct('OCCUPANT_NOT_FOUND', $message, 404);
    }
}
