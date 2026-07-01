<?php

declare(strict_types=1);

namespace Directorio\Domain\Exceptions;

use Urbania\Shared\Domain\Exceptions\DomainException;

final class DuplicateContactDocumentException extends DomainException
{
    public function __construct(string $message = 'Ya existe un contacto activo con ese documento')
    {
        parent::__construct('DUPLICATE_CONTACT_DOCUMENT', $message, 409);
    }
}
