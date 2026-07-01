<?php

declare(strict_types=1);

namespace Urbania\Authorization\Domain\Exceptions;

use Urbania\Shared\Domain\Exceptions\DomainException;

final class RoleNotFoundException extends DomainException
{
    public function __construct(string $message = 'Rol no encontrado')
    {
        parent::__construct('ROLE_NOT_FOUND', $message, 404);
    }
}
