<?php

declare(strict_types=1);

namespace Urbania\Comunicaciones\Application\DTOs;

final readonly class SurveyListItemDto
{
    public function __construct(
        public string $id,
        public string $pregunta,
        public string $tipo,
        public ?string $cierraEl,
        public bool $activa,
        public int $optionsCount,
        public int $responsesCount,
        public string $createdAt,
    ) {}
}
