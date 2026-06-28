<?php

declare(strict_types=1);

namespace Urbania\Propiedades\Domain\Exceptions;

use Urbania\Shared\Domain\Exceptions\DomainException;

final class TowerNameAlreadyExistsException extends DomainException
{
    public function __construct(string $message = 'Ya existe una torre con ese nombre en el condominio')
    {
        parent::__construct('TOWER_NAME_ALREADY_EXISTS', $message, 409);
    }
}
