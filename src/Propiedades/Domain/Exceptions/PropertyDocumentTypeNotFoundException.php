<?php

declare(strict_types=1);

namespace Urbania\Propiedades\Domain\Exceptions;

use Urbania\Shared\Domain\Exceptions\DomainException;

final class PropertyDocumentTypeNotFoundException extends DomainException
{
    public function __construct(string $message = 'Tipo de documento no encontrado')
    {
        parent::__construct('PROPERTY_DOCUMENT_TYPE_NOT_FOUND', $message, 404);
    }
}
