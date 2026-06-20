<?php

declare(strict_types=1);

namespace Urbania\Auth\Domain\Exceptions;

use Urbania\Shared\Domain\Exceptions\DomainException;

final class MfaInvalidCodeException extends DomainException
{
    public function __construct(string $message = 'Invalid or expired MFA code')
    {
        parent::__construct('MFA_INVALID_CODE', $message, 401);
    }
}
