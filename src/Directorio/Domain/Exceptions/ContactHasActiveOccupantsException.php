<?php

declare(strict_types=1);

namespace Directorio\Domain\Exceptions;

use Urbania\Shared\Domain\Exceptions\DomainException;

final class ContactHasActiveOccupantsException extends DomainException
{
    public function __construct(string $message = 'El contacto tiene vínculos activos y no puede eliminarse')
    {
        parent::__construct('CONTACT_HAS_ACTIVE_OCCUPANTS', $message, 409);
    }
}
