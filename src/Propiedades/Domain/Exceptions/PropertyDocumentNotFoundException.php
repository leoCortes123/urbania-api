<?php

declare(strict_types=1);

namespace Urbania\Propiedades\Domain\Exceptions;

use Urbania\Shared\Domain\Exceptions\DomainException;

final class PropertyDocumentNotFoundException extends DomainException
{
    public function __construct(string $message = 'Documento no encontrado')
    {
        parent::__construct('PROPERTY_DOCUMENT_NOT_FOUND', $message, 404);
    }
}
