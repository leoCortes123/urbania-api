<?php

declare(strict_types=1);

namespace Urbania\Comunicaciones\Application\DTOs;

use Urbania\Comunicaciones\Domain\ValueObjects\SurveyType;
use Urbania\Shared\Domain\ValueObjects\Uuid;

final readonly class CreateSurveyDto
{
    /**
     * @param  array<string>  $opciones
     */
    public function __construct(
        public Uuid $condominiumId,
        public string $pregunta,
        public SurveyType $tipo,
        public ?\DateTimeImmutable $cierraEl,
        public array $opciones,
    ) {}
}
