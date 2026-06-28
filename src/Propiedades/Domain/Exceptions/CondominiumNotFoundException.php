<?php

declare(strict_types=1);

namespace Urbania\Propiedades\Domain\Exceptions;

use Urbania\Shared\Domain\Exceptions\DomainException;

final class CondominiumNotFoundException extends DomainException
{
    public function __construct(string $message = 'Condominio no encontrado')
    {
        parent::__construct('CONDOMINIUM_NOT_FOUND', $message, 404);
    }
}
