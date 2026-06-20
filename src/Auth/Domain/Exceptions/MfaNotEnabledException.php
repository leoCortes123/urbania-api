<?php

declare(strict_types=1);

namespace Urbania\Auth\Domain\Exceptions;

use Urbania\Shared\Domain\Exceptions\DomainException;

final class MfaNotEnabledException extends DomainException
{
    public function __construct(string $message = 'MFA is not enabled')
    {
        parent::__construct('MFA_NOT_ENABLED', $message, 400);
    }
}
