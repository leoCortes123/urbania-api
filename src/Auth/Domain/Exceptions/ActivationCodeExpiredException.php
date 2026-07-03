<?php

declare(strict_types=1);

namespace Urbania\Auth\Domain\Exceptions;

use Urbania\Shared\Domain\Exceptions\DomainException;

final class ActivationCodeExpiredException extends DomainException
{
    public function __construct(string $message = 'El código de activación ha expirado. Solicita un nuevo código al administrador.')
    {
        parent::__construct('ACTIVATION_CODE_EXPIRED', $message, 400);
    }
}
