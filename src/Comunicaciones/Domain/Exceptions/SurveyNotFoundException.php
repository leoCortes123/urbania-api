<?php

declare(strict_types=1);

namespace Urbania\Comunicaciones\Domain\Exceptions;

use Urbania\Shared\Domain\Exceptions\DomainException;

final class SurveyNotFoundException extends DomainException
{
    public function __construct()
    {
        parent::__construct(
            message: 'La encuesta no fue encontrada',
            errorCode: 'SURVEY_NOT_FOUND',
            httpStatusCode: 404,
        );
    }
}
