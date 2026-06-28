<?php

declare(strict_types=1);

namespace Urbania\Propiedades\Domain\Exceptions;

use Urbania\Shared\Domain\Exceptions\DomainException;

final class StatusReasonRequiredException extends DomainException
{
    public function __construct(string $message = 'El motivo del cambio de estado es obligatorio')
    {
        parent::__construct('STATUS_REASON_REQUIRED', $message, 422);
    }
}
