<?php

declare(strict_types=1);

namespace Urbania\Auth\Domain\Exceptions;

use Urbania\Shared\Domain\Exceptions\DomainException;

final class PublicRegistrationDisabledException extends DomainException
{
    public function __construct(string $message = 'El registro público no está disponible. Usa el código de activación que te entregó el administrador.')
    {
        parent::__construct('PUBLIC_REGISTRATION_DISABLED', $message, 403);
    }
}
