<?php

declare(strict_types=1);

namespace Urbania\Propiedades\Application\DTOs;

use Urbania\Propiedades\Domain\Entities\PropertyDocumentTypeEntity;

final readonly class PropertyDocumentTypeResponseDto
{
    public function __construct(
        public string $id,
        public string $code,
        public string $name,
        public ?string $description,
        public int $sortOrder,
        public bool $isActive,
        public string $createdAt,
        public string $updatedAt,
    ) {}

    public static function fromEntity(PropertyDocumentTypeEntity $entity): self
    {
        return new self(
            id: $entity->id()->toString(),
            code: $entity->code(),
            name: $entity->name(),
            description: $entity->description(),
            sortOrder: $entity->sortOrder(),
            isActive: $entity->isActive(),
            createdAt: $entity->createdAt()->format('c'),
            updatedAt: $entity->updatedAt()->format('c'),
        );
    }
}
