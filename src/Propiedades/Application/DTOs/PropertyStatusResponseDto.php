<?php

declare(strict_types=1);

namespace Urbania\Propiedades\Application\DTOs;

use Urbania\Propiedades\Domain\Entities\PropertyStatusEntity;

final readonly class PropertyStatusResponseDto
{
    public function __construct(
        public string $id,
        public string $code,
        public string $name,
        public ?string $description,
        public bool $allowsResidents,
        public bool $isActive,
        public int $sortOrder,
        public string $createdAt,
        public string $updatedAt,
    ) {}

    public static function fromEntity(PropertyStatusEntity $entity): self
    {
        return new self(
            id: $entity->id()->toString(),
            code: $entity->code(),
            name: $entity->name(),
            description: $entity->description(),
            allowsResidents: $entity->allowsResidents(),
            isActive: $entity->isActive(),
            sortOrder: $entity->sortOrder(),
            createdAt: $entity->createdAt()->format('c'),
            updatedAt: $entity->updatedAt()->format('c'),
        );
    }
}
