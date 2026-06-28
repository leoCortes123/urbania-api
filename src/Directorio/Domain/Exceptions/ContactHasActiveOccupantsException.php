<?php

declare(strict_types=1);

namespace Directorio\Domain\Exceptions;

class ContactHasActiveOccupantsException extends \DomainException
{
    public function __construct(string $contactId)
    {
        parent::__construct(
            "El contacto {$contactId} tiene vínculos activos como propietario y no puede eliminarse",
            409
        );
    }
}
