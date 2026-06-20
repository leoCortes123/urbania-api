<?php

declare(strict_types=1);

namespace Urbania\Auth\Domain\Exceptions;

use Urbania\Shared\Domain\Exceptions\DomainException;

final class MfaAlreadyEnabledException extends DomainException
{
    public function __construct(string $message = 'MFA is already enabled')
    {
        parent::__construct('MFA_ALREADY_ENABLED', $message, 409);
    }
}
