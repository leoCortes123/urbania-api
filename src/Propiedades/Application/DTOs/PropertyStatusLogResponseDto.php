<?php

declare(strict_types=1);

namespace Urbania\Propiedades\Application\DTOs;

use Urbania\Propiedades\Domain\Entities\PropertyStatusLogEntry;

final readonly class PropertyStatusLogResponseDto
{
    /**
     * @param  array<string, mixed>|null  $fromStatus
     * @param  array<string, mixed>  $toStatus
     * @param  array<string, mixed>  $changedBy
     */
    public function __construct(
        public string $id,
        public string $propertyId,
        public ?array $fromStatus,
        public array $toStatus,
        public array $changedBy,
        public string $reason,
        public string $createdAt,
    ) {}

    public static function fromEntity(PropertyStatusLogEntry $entity): self
    {
        return new self(
            id: $entity->id()->toString(),
            propertyId: $entity->propertyId()->toString(),
            fromStatus: null,
            toStatus: ['id' => $entity->toStatusId()->toString()],
            changedBy: ['id' => $entity->changedByUserId()->toString()],
            reason: $entity->reason(),
            createdAt: $entity->createdAt()->format('c'),
        );
    }
}
