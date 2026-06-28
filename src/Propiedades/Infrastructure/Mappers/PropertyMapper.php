<?php

declare(strict_types=1);

namespace Urbania\Propiedades\Infrastructure\Mappers;

use App\Models\Property as PropertyModel;
use Urbania\Propiedades\Domain\Entities\PropertyEntity;
use Urbania\Shared\Domain\ValueObjects\Uuid;

final readonly class PropertyMapper
{
    public function toDomain(PropertyModel $model): PropertyEntity
    {
        return PropertyEntity::reconstitute(
            id: Uuid::fromString($model->id),
            condominiumId: Uuid::fromString($model->condominium_id),
            towerId: Uuid::fromString($model->tower_id),
            propertyTypeId: Uuid::fromString($model->property_type_id),
            propertyStatusId: Uuid::fromString($model->property_status_id),
            floor: (int) $model->floor,
            unitNumber: $model->unit_number,
            areaM2: (string) $model->area_m2,
            coefficient: (string) $model->coefficient,
            bedrooms: $model->bedrooms,
            bathrooms: $model->bathrooms,
            hasParking: (bool) $model->has_parking,
            parkingLot: $model->parking_lot,
            notes: $model->notes,
            createdAt: $this->toDateTimeImmutable($model->created_at),
            updatedAt: $this->toDateTimeImmutable($model->updated_at),
            deletedAt: $model->deleted_at === null ? null : $this->toDateTimeImmutable($model->deleted_at),
        );
    }

    /**
     * @return array<string, mixed>
     */
    public function toPersistence(PropertyEntity $entity): array
    {
        return [
            'id' => $entity->id()->toString(),
            'condominium_id' => $entity->condominiumId()->toString(),
            'tower_id' => $entity->towerId()->toString(),
            'property_type_id' => $entity->propertyTypeId()->toString(),
            'property_status_id' => $entity->propertyStatusId()->toString(),
            'floor' => $entity->floor(),
            'unit_number' => $entity->unitNumber(),
            'area_m2' => $entity->areaM2(),
            'coefficient' => $entity->coefficient(),
            'bedrooms' => $entity->bedrooms(),
            'bathrooms' => $entity->bathrooms(),
            'has_parking' => $entity->hasParking(),
            'parking_lot' => $entity->parkingLot(),
            'notes' => $entity->notes(),
            'created_at' => $entity->createdAt()->format('Y-m-d H:i:s'),
            'updated_at' => $entity->updatedAt()->format('Y-m-d H:i:s'),
        ];
    }

    /**
     * @param  array<PropertyModel>  $models
     * @return array<PropertyEntity>
     */
    public function toDomainArray(array $models): array
    {
        return array_map(fn (PropertyModel $model): PropertyEntity => $this->toDomain($model), $models);
    }

    private function toDateTimeImmutable(mixed $value): \DateTimeImmutable
    {
        assert($value instanceof \DateTimeInterface);

        return \DateTimeImmutable::createFromMutable($value instanceof \DateTime ? $value : \DateTime::createFromImmutable($value));
    }
}
