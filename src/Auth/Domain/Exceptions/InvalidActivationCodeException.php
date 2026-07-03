<?php

declare(strict_types=1);

namespace Urbania\Auth\Domain\Exceptions;

use Urbania\Shared\Domain\Exceptions\DomainException;

final class InvalidActivationCodeException extends DomainException
{
    public function __construct(string $message = 'El código de activación es incorrecto')
    {
        parent::__construct('INVALID_ACTIVATION_CODE', $message, 400);
    }
}
