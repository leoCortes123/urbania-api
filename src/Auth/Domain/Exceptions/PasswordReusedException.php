<?php

declare(strict_types=1);

namespace Urbania\Auth\Domain\Exceptions;

use Urbania\Shared\Domain\Exceptions\DomainException;

final class PasswordReusedException extends DomainException
{
    public function __construct(string $message = 'Password has been used recently')
    {
        parent::__construct('PASSWORD_REUSED', $message, 400);
    }
}
