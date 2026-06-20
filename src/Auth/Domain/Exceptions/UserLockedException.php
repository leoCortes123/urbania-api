<?php

declare(strict_types=1);

namespace Urbania\Auth\Domain\Exceptions;

use Urbania\Shared\Domain\Exceptions\DomainException;

final class UserLockedException extends DomainException
{
    public function __construct(string $message = 'Account is locked due to too many failed attempts')
    {
        parent::__construct('USER_LOCKED', $message, 401);
    }
}
