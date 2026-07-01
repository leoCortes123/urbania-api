<?php

declare(strict_types=1);

namespace Urbania\Authorization\Domain\Exceptions;

use Urbania\Shared\Domain\Exceptions\DomainException;

final class AuthorizationContextException extends DomainException
{
    public function __construct(string $message = 'Contexto de autorización inválido')
    {
        parent::__construct('AUTHORIZATION_CONTEXT_INVALID', $message, 403);
    }
}
