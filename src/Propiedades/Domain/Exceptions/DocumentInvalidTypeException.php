<?php

declare(strict_types=1);

namespace Urbania\Propiedades\Domain\Exceptions;

use Urbania\Shared\Domain\Exceptions\DomainException;

final class DocumentInvalidTypeException extends DomainException
{
    public function __construct(string $message = 'Tipo de archivo no permitido. Solo se aceptan PDF, JPG, JPEG y PNG')
    {
        parent::__construct('DOCUMENT_INVALID_TYPE', $message, 415);
    }
}
