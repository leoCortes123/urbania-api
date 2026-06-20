<?php

declare(strict_types=1);

namespace Urbania\Auth\Domain\Exceptions;

use Urbania\Shared\Domain\Exceptions\DomainException;

final class MfaRequiredException extends DomainException
{
    public function __construct(string $message = 'Multi-factor authentication required')
    {
        parent::__construct('MFA_REQUIRED', $message, 401);
    }
}
