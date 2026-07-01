<?php

declare(strict_types=1);

namespace Urbania\Comunicaciones\Domain\Exceptions;

use Urbania\Shared\Domain\Exceptions\DomainException;

final class AnnouncementNotFoundException extends DomainException
{
    public function __construct()
    {
        parent::__construct(
            message: 'El comunicado no fue encontrado',
            errorCode: 'ANNOUNCEMENT_NOT_FOUND',
            httpStatusCode: 404,
        );
    }
}
