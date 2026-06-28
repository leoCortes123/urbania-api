<?php

declare(strict_types=1);

namespace Urbania\Propiedades\Domain\Exceptions;

use Urbania\Shared\Domain\Exceptions\DomainException;

final class TowerHasPropertiesException extends DomainException
{
    public function __construct(string $message = 'La torre tiene unidades asociadas')
    {
        parent::__construct('TOWER_HAS_PROPERTIES', $message, 409);
    }
}
