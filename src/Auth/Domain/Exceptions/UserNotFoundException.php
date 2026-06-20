<?php

declare(strict_types=1);

namespace Urbania\Auth\Domain\Exceptions;

use Urbania\Shared\Domain\Exceptions\DomainException;

final class UserNotFoundException extends DomainException
{
    public function __construct(string $message = 'User not found')
    {
        parent::__construct('USER_NOT_FOUND', $message, 404);
    }
}
