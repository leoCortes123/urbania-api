<?php

declare(strict_types=1);

namespace Urbania\Auth\Domain\Exceptions;

use Urbania\Shared\Domain\Exceptions\DomainException;

final class AccountPendingActivationException extends DomainException
{
    public function __construct(string $message = 'La cuenta no ha sido activada. Usa el código de activación que te entregó el administrador.')
    {
        parent::__construct('ACCOUNT_PENDING_ACTIVATION', $message, 403);
    }
}
