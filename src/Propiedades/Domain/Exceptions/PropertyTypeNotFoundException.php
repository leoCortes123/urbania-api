<?php

declare(strict_types=1);

namespace Urbania\Propiedades\Domain\Exceptions;

use Urbania\Shared\Domain\Exceptions\DomainException;

final class PropertyTypeNotFoundException extends DomainException
{
    public function __construct(string $message = 'Tipo de propiedad no encontrado')
    {
        parent::__construct('PROPERTY_TYPE_NOT_FOUND', $message, 404);
    }
}
