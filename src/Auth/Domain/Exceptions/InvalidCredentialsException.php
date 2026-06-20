<?php

declare(strict_types=1);

namespace Urbania\Auth\Domain\Exceptions;

use Urbania\Shared\Domain\Exceptions\DomainException;

final class InvalidCredentialsException extends DomainException
{
    public function __construct(string $message = 'Invalid email or password')
    {
        parent::__construct('INVALID_CREDENTIALS', $message, 401);
    }

    public static function weakPassword(): self
    {
        return new self('Password must be at least 8 characters');
    }
}
