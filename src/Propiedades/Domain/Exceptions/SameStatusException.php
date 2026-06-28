<?php

declare(strict_types=1);

namespace Urbania\Propiedades\Domain\Exceptions;

use Urbania\Shared\Domain\Exceptions\DomainException;

final class SameStatusException extends DomainException
{
    public function __construct(string $message = 'La unidad ya se encuentra en ese estado')
    {
        parent::__construct('SAME_STATUS', $message, 400);
    }
}
