<?php

declare(strict_types=1);

namespace Urbania\Propiedades\Domain\Exceptions;

use Urbania\Shared\Domain\Exceptions\DomainException;

final class PropertyStatusNotFoundException extends DomainException
{
    public function __construct(string $message = 'Estado de propiedad no encontrado')
    {
        parent::__construct('PROPERTY_STATUS_NOT_FOUND', $message, 404);
    }
}
