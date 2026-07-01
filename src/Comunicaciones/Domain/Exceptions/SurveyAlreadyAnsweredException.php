<?php

declare(strict_types=1);

namespace Urbania\Comunicaciones\Domain\Exceptions;

use Urbania\Shared\Domain\Exceptions\DomainException;

final class SurveyAlreadyAnsweredException extends DomainException
{
    public function __construct()
    {
        parent::__construct(
            message: 'El contacto ya respondió esta encuesta',
            errorCode: 'ALREADY_ANSWERED',
            httpStatusCode: 409,
        );
    }
}
