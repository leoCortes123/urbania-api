<?php

declare(strict_types=1);

namespace Urbania\Propiedades\Domain\Exceptions;

use Urbania\Shared\Domain\Exceptions\DomainException;

final class PropertyDocumentTypeCodeAlreadyExistsException extends DomainException
{
    public function __construct(string $message = 'El código del tipo de documento ya existe')
    {
        parent::__construct('PROPERTY_DOCUMENT_TYPE_CODE_ALREADY_EXISTS', $message, 409);
    }
}
