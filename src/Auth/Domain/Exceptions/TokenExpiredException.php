<?php

declare(strict_types=1);

namespace Urbania\Auth\Domain\Exceptions;

use Urbania\Shared\Domain\Exceptions\DomainException;

final class TokenExpiredException extends DomainException
{
    public function __construct(string $message = 'Token has expired')
    {
        parent::__construct('TOKEN_EXPIRED', $message, 401);
    }
}
