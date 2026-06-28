<?php

declare(strict_types=1);

namespace Urbania\Propiedades\Domain\Exceptions;

use Urbania\Shared\Domain\Exceptions\DomainException;

final class PropertyNotFoundException extends DomainException
{
    public function __construct(string $message = 'Unidad no encontrada')
    {
        parent::__construct('PROPERTY_NOT_FOUND', $message, 404);
    }
}
