<?php

declare(strict_types=1);

namespace Urbania\Comunicaciones\Domain\Exceptions;

use Urbania\Shared\Domain\Exceptions\DomainException;

final class TemplateNotFoundException extends DomainException
{
    public function __construct()
    {
        parent::__construct(
            message: 'La plantilla no fue encontrada',
            errorCode: 'TEMPLATE_NOT_FOUND',
            httpStatusCode: 404,
        );
    }
}
