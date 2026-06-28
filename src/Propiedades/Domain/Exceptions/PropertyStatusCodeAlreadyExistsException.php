<?php

declare(strict_types=1);

namespace Urbania\Propiedades\Domain\Exceptions;

use Urbania\Shared\Domain\Exceptions\DomainException;

final class PropertyStatusCodeAlreadyExistsException extends DomainException
{
    public function __construct(string $message = 'El código de estado de propiedad ya existe')
    {
        parent::__construct('PROPERTY_STATUS_CODE_ALREADY_EXISTS', $message, 409);
    }
}
