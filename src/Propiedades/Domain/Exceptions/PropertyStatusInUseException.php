<?php

declare(strict_types=1);

namespace Urbania\Propiedades\Domain\Exceptions;

use Urbania\Shared\Domain\Exceptions\DomainException;

final class PropertyStatusInUseException extends DomainException
{
    public function __construct(string $message = 'El estado de propiedad está en uso y no puede ser desactivado')
    {
        parent::__construct('PROPERTY_STATUS_IN_USE', $message, 409);
    }
}
