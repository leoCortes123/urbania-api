<?php

declare(strict_types=1);

namespace Urbania\Propiedades\Domain\Exceptions;

use Urbania\Shared\Domain\Exceptions\DomainException;

final class DocumentTooLargeException extends DomainException
{
    public function __construct(string $message = 'El archivo excede el tamaño máximo permitido de 20 MB')
    {
        parent::__construct('DOCUMENT_TOO_LARGE', $message, 413);
    }
}
