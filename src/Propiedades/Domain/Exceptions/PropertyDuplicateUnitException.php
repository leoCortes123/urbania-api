<?php

declare(strict_types=1);

namespace Urbania\Propiedades\Domain\Exceptions;

use Urbania\Shared\Domain\Exceptions\DomainException;

final class PropertyDuplicateUnitException extends DomainException
{
    public function __construct(string $message = 'Ya existe una unidad con ese piso y número en la torre')
    {
        parent::__construct('PROPERTY_DUPLICATE_UNIT', $message, 409);
    }
}
