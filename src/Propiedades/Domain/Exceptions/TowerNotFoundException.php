<?php

declare(strict_types=1);

namespace Urbania\Propiedades\Domain\Exceptions;

use Urbania\Shared\Domain\Exceptions\DomainException;

final class TowerNotFoundException extends DomainException
{
    public function __construct(string $message = 'Torre no encontrada')
    {
        parent::__construct('TOWER_NOT_FOUND', $message, 404);
    }
}
