<?php

declare(strict_types=1);

namespace Urbania\Auth\Domain\Exceptions;

use Urbania\Shared\Domain\Exceptions\DomainException;

final class EmailAlreadyExistsException extends DomainException
{
    public function __construct(string $message = 'Email already registered')
    {
        parent::__construct('EMAIL_ALREADY_EXISTS', $message, 409);
    }
}
