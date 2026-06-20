<?php

declare(strict_types=1);

namespace Urbania\Auth\Domain\Exceptions;

use Urbania\Shared\Domain\Exceptions\DomainException;

final class MfaNotConfiguredException extends DomainException
{
    public function __construct(string $message = 'MFA is not configured')
    {
        parent::__construct('MFA_NOT_CONFIGURED', $message, 400);
    }
}
