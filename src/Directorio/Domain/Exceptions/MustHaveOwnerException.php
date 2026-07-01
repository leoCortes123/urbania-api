<?php

declare(strict_types=1);

namespace Directorio\Domain\Exceptions;

use Urbania\Shared\Domain\Exceptions\DomainException;

final class MustHaveOwnerException extends DomainException
{
    public function __construct(string $message = 'La unidad debe tener al menos un propietario activo')
    {
        parent::__construct('MUST_HAVE_OWNER', $message, 409);
    }
}
