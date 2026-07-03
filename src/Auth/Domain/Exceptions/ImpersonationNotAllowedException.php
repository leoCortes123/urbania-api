<?php

declare(strict_types=1);

namespace Urbania\Auth\Domain\Exceptions;

use Urbania\Shared\Domain\Exceptions\DomainException;

final class ImpersonationNotAllowedException extends DomainException
{
    public function __construct(string $message = 'No tienes permiso para suplantar usuarios.')
    {
        parent::__construct('IMPERSONATION_NOT_ALLOWED', $message, 403);
    }
}
