<?php

declare(strict_types=1);

namespace Urbania\Authorization\Domain\Exceptions;

use Urbania\Shared\Domain\Exceptions\DomainException;

final class RoleIsSystemException extends DomainException
{
    public function __construct(string $message = 'Los roles de sistema no pueden modificarse')
    {
        parent::__construct('ROLE_IS_SYSTEM', $message, 403);
    }
}
