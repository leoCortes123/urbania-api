<?php

declare(strict_types=1);

namespace Urbania\Auth\Domain\Exceptions;

use Urbania\Shared\Domain\Exceptions\DomainException;

final class TokenInvalidException extends DomainException
{
    public function __construct(string $message = 'Token is invalid')
    {
        parent::__construct('TOKEN_INVALID', $message, 401);
    }
}
