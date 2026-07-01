<?php

declare(strict_types=1);

namespace Urbania\Authorization\Domain\Exceptions;

use Urbania\Shared\Domain\Exceptions\DomainException;

final class AssignmentNotFoundException extends DomainException
{
    public function __construct(string $message = 'Asignación de rol no encontrada')
    {
        parent::__construct('ASSIGNMENT_NOT_FOUND', $message, 404);
    }
}
