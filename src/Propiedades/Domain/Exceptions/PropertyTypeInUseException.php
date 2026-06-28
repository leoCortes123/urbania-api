<?php

declare(strict_types=1);

namespace Urbania\Propiedades\Domain\Exceptions;

use Urbania\Shared\Domain\Exceptions\DomainException;

final class PropertyTypeInUseException extends DomainException
{
    public function __construct(string $message = 'El tipo de propiedad está en uso y no puede ser desactivado')
    {
        parent::__construct('PROPERTY_TYPE_IN_USE', $message, 409);
    }
}
