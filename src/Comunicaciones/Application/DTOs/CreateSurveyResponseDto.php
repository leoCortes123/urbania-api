<?php

declare(strict_types=1);

namespace Urbania\Comunicaciones\Application\DTOs;

use Urbania\Shared\Domain\ValueObjects\Uuid;

final readonly class CreateSurveyResponseDto
{
    public function __construct(
        public Uuid $surveyId,
        public Uuid $contactId,
        public Uuid $optionId,
    ) {}
}
