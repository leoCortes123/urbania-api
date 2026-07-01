<?php

declare(strict_types=1);

namespace Directorio\Domain\Exceptions;

use Urbania\Shared\Domain\Exceptions\DomainException;

final class ContactNotFoundException extends DomainException
{
    public function __construct(string $message = 'Contacto no encontrado')
    {
        parent::__construct('CONTACT_NOT_FOUND', $message, 404);
    }
}
