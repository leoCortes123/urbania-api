<?php

declare(strict_types=1);

namespace Urbania\Propiedades\Domain\Exceptions;

use Urbania\Shared\Domain\Exceptions\DomainException;

final class PropertyDocumentTypeInUseException extends DomainException
{
    public function __construct(string $message = 'El tipo de documento está en uso')
    {
        parent::__construct('PROPERTY_DOCUMENT_TYPE_IN_USE', $message, 409);
    }
}
