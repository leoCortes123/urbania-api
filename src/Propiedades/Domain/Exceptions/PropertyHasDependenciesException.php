<?php

declare(strict_types=1);

namespace Urbania\Propiedades\Domain\Exceptions;

use Urbania\Shared\Domain\Exceptions\DomainException;

final class PropertyHasDependenciesException extends DomainException
{
    /**
     * @param  array<string, mixed>  $details
     */
    public function __construct(
        string $message = 'La unidad tiene dependencias activas',
        public readonly array $details = [],
    ) {
        parent::__construct('PROPERTY_HAS_DEPENDENCIES', $message, 409);
    }
}
