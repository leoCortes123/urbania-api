<?php

declare(strict_types=1);

namespace Urbania\Comunicaciones\Domain\Exceptions;

use Urbania\Shared\Domain\Exceptions\DomainException;

final class ChannelNotConfiguredException extends DomainException
{
    public function __construct(string $canal)
    {
        parent::__construct(
            message: "El canal '{$canal}' no está configurado o activo",
            errorCode: 'NO_ACTIVE_CHANNEL',
            httpStatusCode: 422,
        );
    }
}
