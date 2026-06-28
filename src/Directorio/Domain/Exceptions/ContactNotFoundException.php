<?php

declare(strict_types=1);

namespace Directorio\Domain\Exceptions;

class ContactNotFoundException extends \DomainException
{
    public function __construct(string $id)
    {
        parent::__construct("Contacto no encontrado: {$id}", 404);
    }
}
