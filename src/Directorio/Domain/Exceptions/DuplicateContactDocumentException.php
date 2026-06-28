<?php

declare(strict_types=1);

namespace Directorio\Domain\Exceptions;

class DuplicateContactDocumentException extends \DomainException
{
    public function __construct(string $documentType, string $documentNumber)
    {
        parent::__construct(
            "Ya existe un contacto activo con {$documentType} {$documentNumber}",
            409
        );
    }
}
