<?php

declare(strict_types=1);

namespace Urbania\Comunicaciones\Domain\Exceptions;

use Urbania\Shared\Domain\Exceptions\DomainException;

final class SurveyClosedException extends DomainException
{
    public function __construct()
    {
        parent::__construct(
            message: 'La encuesta está cerrada',
            errorCode: 'SURVEY_CLOSED',
            httpStatusCode: 422,
        );
    }
}
