<?php

declare(strict_types=1);

namespace Directorio\Domain\Exceptions;

class MustHaveOwnerException extends \DomainException
{
    public function __construct(string $propertyId)
    {
        parent::__construct(
            "La unidad {$propertyId} debe tener al menos un propietario activo",
            409
        );
    }
}
