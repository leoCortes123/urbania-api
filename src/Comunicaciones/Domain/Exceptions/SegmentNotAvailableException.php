<?php

declare(strict_types=1);

namespace Urbania\Comunicaciones\Domain\Exceptions;

use Urbania\Shared\Domain\Exceptions\DomainException;

final class SegmentNotAvailableException extends DomainException
{
    public function __construct(string $segmento)
    {
        parent::__construct(
            message: "El segmento '{$segmento}' no está disponible",
            errorCode: 'SEGMENT_NOT_AVAILABLE',
            httpStatusCode: 422,
        );
    }
}
