<?php

declare(strict_types=1);

namespace Urbania\Auth\Domain\Exceptions;

use Urbania\Shared\Domain\Exceptions\DomainException;

final class EmailAlreadyVerifiedException extends DomainException
{
    public function __construct(string $message = 'Email is already verified')
    {
        parent::__construct('EMAIL_ALREADY_VERIFIED', $message, 409);
    }
}
