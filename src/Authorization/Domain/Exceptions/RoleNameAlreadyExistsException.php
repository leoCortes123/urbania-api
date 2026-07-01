<?php

declare(strict_types=1);

namespace Urbania\Authorization\Domain\Exceptions;

use Urbania\Shared\Domain\Exceptions\DomainException;

final class RoleNameAlreadyExistsException extends DomainException
{
    public function __construct(string $message = 'Ya existe un rol con ese nombre en la organización')
    {
        parent::__construct('ROLE_NAME_ALREADY_EXISTS', $message, 409);
    }
}
