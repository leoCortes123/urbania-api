<?php

declare(strict_types=1);

namespace Urbania\Auth\Domain\Exceptions;

use Urbania\Shared\Domain\Exceptions\DomainException;

final class EmailVerificationInvalidException extends DomainException
{
    public function __construct(string $message = 'Invalid email verification token')
    {
        parent::__construct('EMAIL_VERIFICATION_INVALID', $message, 400);
    }
}
