<?php

declare(strict_types=1);

namespace Urbania\Propiedades\Domain\Exceptions;

use Urbania\Shared\Domain\Exceptions\DomainException;

final class PropertyTypeCodeAlreadyExistsException extends DomainException
{
    public function __construct(string $message = 'El código de tipo de propiedad ya existe')
    {
        parent::__construct('PROPERTY_TYPE_CODE_ALREADY_EXISTS', $message, 409);
    }
}
