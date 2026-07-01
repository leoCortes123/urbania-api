<?php

declare(strict_types=1);

namespace Urbania\Comunicaciones\Application\DTOs;

use Urbania\Comunicaciones\Domain\Entities\SurveyEntity;

final readonly class SurveyDto
{
    /**
     * @param  array<string>  $opciones
     */
    public function __construct(
        public string $id,
        public string $condominiumId,
        public string $pregunta,
        public string $tipo,
        public ?string $cierraEl,
        public bool $activa,
        public array $opciones,
        public string $createdAt,
        public string $updatedAt,
    ) {}

    /**
     * @param  array<string>  $opciones
     */
    public static function fromEntity(SurveyEntity $entity, array $opciones = []): self
    {
        return new self(
            id: $entity->id()->toString(),
            condominiumId: $entity->condominiumId()->toString(),
            pregunta: $entity->pregunta(),
            tipo: $entity->tipo()->value,
            cierraEl: $entity->cierraEl()?->format('c'),
            activa: $entity->activa(),
            opciones: $opciones,
            createdAt: $entity->createdAt()->format('c'),
            updatedAt: $entity->updatedAt()->format('c'),
        );
    }
}
