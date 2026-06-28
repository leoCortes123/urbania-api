<?php

declare(strict_types=1);

namespace Urbania\Propiedades\Infrastructure\Mappers;

use App\Models\PropertyDocumentType as PropertyDocumentTypeModel;
use Urbania\Propiedades\Domain\Entities\PropertyDocumentTypeEntity;
use Urbania\Shared\Domain\ValueObjects\Uuid;

final readonly class PropertyDocumentTypeMapper
{
    public function toDomain(PropertyDocumentTypeModel $model): PropertyDocumentTypeEntity
    {
        return PropertyDocumentTypeEntity::reconstitute(
            id: Uuid::fromString($model->id),
            code: $model->code,
            name: $model->name,
            description: $model->description,
            sortOrder: (int) $model->sort_order,
            isActive: (bool) $model->is_active,
            createdAt: $this->toDateTimeImmutable($model->created_at),
            updatedAt: $this->toDateTimeImmutable($model->updated_at),
        );
    }

    /**
     * @return array<string, mixed>
     */
    public function toPersistence(PropertyDocumentTypeEntity $entity): array
    {
        return [
            'id' => $entity->id()->toString(),
            'code' => $entity->code(),
            'name' => $entity->name(),
            'description' => $entity->description(),
            'sort_order' => $entity->sortOrder(),
            'is_active' => $entity->isActive(),
            'created_at' => $entity->createdAt()->format('Y-m-d H:i:s'),
            'updated_at' => $entity->updatedAt()->format('Y-m-d H:i:s'),
        ];
    }

    /**
     * @param  array<PropertyDocumentTypeModel>  $models
     * @return array<PropertyDocumentTypeEntity>
     */
    public function toDomainArray(array $models): array
    {
        return array_map(fn (PropertyDocumentTypeModel $model): PropertyDocumentTypeEntity => $this->toDomain($model), $models);
    }

    private function toDateTimeImmutable(mixed $value): \DateTimeImmutable
    {
        assert($value instanceof \DateTimeInterface);

        return \DateTimeImmutable::createFromMutable($value instanceof \DateTime ? $value : \DateTime::createFromImmutable($value));
    }
}
