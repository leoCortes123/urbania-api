<?php

declare(strict_types=1);

namespace Urbania\Auth\Domain\Exceptions;

use Urbania\Shared\Domain\Exceptions\DomainException;

final class AccountAlreadyActiveException extends DomainException
{
    public function __construct(string $message = 'La cuenta ya está activada')
    {
        parent::__construct('ACCOUNT_ALREADY_ACTIVE', $message, 422);
    }
}
