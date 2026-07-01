<?php

declare(strict_types=1);

namespace Urbania\Authorization\Domain\Exceptions;

use Urbania\Shared\Domain\Exceptions\DomainException;

final class AssignmentAlreadyExistsException extends DomainException
{
    public function __construct(string $message = 'El usuario ya tiene asignado ese rol en el alcance indicado')
    {
        parent::__construct('ASSIGNMENT_ALREADY_EXISTS', $message, 409);
    }
}
