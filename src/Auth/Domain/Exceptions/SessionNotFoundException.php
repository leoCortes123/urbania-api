<?php

declare(strict_types=1);

namespace Urbania\Auth\Domain\Exceptions;

use Urbania\Shared\Domain\Exceptions\DomainException;

final class SessionNotFoundException extends DomainException
{
    public function __construct(string $message = 'Session not found')
    {
        parent::__construct('SESSION_NOT_FOUND', $message, 404);
    }
}
