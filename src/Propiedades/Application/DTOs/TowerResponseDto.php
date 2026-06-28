<?php

declare(strict_types=1);

namespace Urbania\Propiedades\Application\DTOs;

use Urbania\Propiedades\Domain\Entities\TowerEntity;

final readonly class TowerResponseDto
{
    public function __construct(
        public string $id,
        public string $condominiumId,
        public string $name,
        public ?string $code,
        public int $floorCount,
        public bool $hasElevator,
        public ?string $description,
        public int $sortOrder,
        public string $createdAt,
        public string $updatedAt,
    ) {}

    public static function fromEntity(TowerEntity $entity): self
    {
        return new self(
            id: $entity->id()->toString(),
            condominiumId: $entity->condominiumId()->toString(),
            name: $entity->name(),
            code: $entity->code(),
            floorCount: $entity->floorCount(),
            hasElevator: $entity->hasElevator(),
            description: $entity->description(),
            sortOrder: $entity->sortOrder(),
            createdAt: $entity->createdAt()->format('c'),
            updatedAt: $entity->updatedAt()->format('c'),
        );
    }
}
