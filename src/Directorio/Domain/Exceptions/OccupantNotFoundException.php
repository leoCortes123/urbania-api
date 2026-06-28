<?php

declare(strict_types=1);

namespace Directorio\Domain\Exceptions;

class OccupantNotFoundException extends \DomainException
{
    public function __construct(string $id)
    {
        parent::__construct("Vínculo de ocupante no encontrado: {$id}", 404);
    }
}
