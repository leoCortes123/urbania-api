<?php

declare(strict_types=1);

namespace Urbania\Auth\Domain\Exceptions;

use Urbania\Shared\Domain\Exceptions\DomainException;

final class NotPortalPrimaryException extends DomainException
{
    public function __construct(string $message = 'Solo el administrador del portal de la unidad puede realizar esta acción.')
    {
        parent::__construct('NOT_PORTAL_PRIMARY', $message, 403);
    }
}
