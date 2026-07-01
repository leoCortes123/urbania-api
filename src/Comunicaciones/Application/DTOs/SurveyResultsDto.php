<?php

declare(strict_types=1);

namespace Urbania\Comunicaciones\Application\DTOs;

final readonly class SurveyResultsDto
{
    /**
     * @param  array<int, array<string, mixed>>  $conteos
     */
    public function __construct(
        public string $surveyId,
        public int $total,
        public array $conteos,
    ) {}
}
