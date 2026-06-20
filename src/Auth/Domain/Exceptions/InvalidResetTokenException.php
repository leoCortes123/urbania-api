<?php

declare(strict_types=1);

namespace Urbania\Auth\Domain\Exceptions;

use Urbania\Shared\Domain\Exceptions\DomainException;

final class InvalidResetTokenException extends DomainException
{
    public function __construct(string $message = 'Invalid or expired reset token')
    {
        parent::__construct('INVALID_RESET_TOKEN', $message, 400);
    }
}
