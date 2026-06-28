<?php

declare(strict_types=1);

namespace Urbania\Propiedades\Infrastructure\Mappers;

use App\Models\PropertyStatus as PropertyStatusModel;
use Urbania\Propiedades\Domain\Entities\PropertyStatusEntity;
use Urbania\Shared\Domain\ValueObjects\Uuid;

final readonly class PropertyStatusMapper
{
    public function toDomain(PropertyStatusModel $model): PropertyStatusEntity
    {
        return PropertyStatusEntity::reconstitute(
            id: Uuid::fromString($model->id),
            code: $model->code,
            name: $model->name,
            description: $model->description,
            allowsResidents: (bool) $model->allows_residents,
            isActive: (bool) $model->is_active,
            sortOrder: (int) $model->sort_order,
            createdAt: $this->toDateTimeImmutable($model->created_at),
            updatedAt: $this->toDateTimeImmutable($model->updated_at),
        );
    }

    /**
     * @return array<string, mixed>
     */
    public function toPersistence(PropertyStatusEntity $entity): array
    {
        return [
            'id' => $entity->id()->toString(),
            'code' => $entity->code(),
            'name' => $entity->name(),
            'description' => $entity->description(),
            'allows_residents' => $entity->allowsResidents(),
            'is_active' => $entity->isActive(),
            'sort_order' => $entity->sortOrder(),
            'created_at' => $entity->createdAt()->format('Y-m-d H:i:s'),
            'updated_at' => $entity->updatedAt()->format('Y-m-d H:i:s'),
        ];
    }

    /**
     * @param  array<PropertyStatusModel>  $models
     * @return array<PropertyStatusEntity>
     */
    public function toDomainArray(array $models): array
    {
        return array_map(fn (PropertyStatusModel $model): PropertyStatusEntity => $this->toDomain($model), $models);
    }

    private function toDateTimeImmutable(mixed $value): \DateTimeImmutable
    {
        assert($value instanceof \DateTimeInterface);

        return \DateTimeImmutable::createFromMutable($value instanceof \DateTime ? $value : \DateTime::createFromImmutable($value));
    }
}
