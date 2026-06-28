<?php

declare(strict_types=1);

namespace Urbania\Propiedades\Domain\Exceptions;

use Urbania\Shared\Domain\Exceptions\DomainException;

final class StatusHasActiveResidentsException extends DomainException
{
    public function __construct(string $message = 'El estado no admite residentes activos')
    {
        parent::__construct('STATUS_HAS_ACTIVE_RESIDENTS', $message, 400);
    }
}
