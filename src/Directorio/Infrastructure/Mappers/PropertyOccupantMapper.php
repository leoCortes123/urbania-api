<?php

declare(strict_types=1);

namespace Directorio\Infrastructure\Mappers;

use App\Models\PropertyOccupant as EloquentPropertyOccupant;
use Directorio\Domain\Entities\PropertyOccupant;

class PropertyOccupantMapper
{
    public static function toDomain(EloquentPropertyOccupant $model): PropertyOccupant
    {
        return new PropertyOccupant(
            id: $model->id,
            propertyId: $model->property_id,
            contactId: $model->contact_id,
            occupantTypeId: $model->occupant_type_id,
            isPrimary: $model->is_primary,
            moveInDate: $model->move_in_date?->toISOString(),
            moveOutDate: $model->move_out_date?->toISOString(),
            isActive: $model->is_active,
            createdAt: $model->created_at?->toISOString(),
            updatedAt: $model->updated_at?->toISOString(),
            deletedAt: $model->deleted_at?->toISOString(),
        );
    }

    /**
     * @param  EloquentPropertyOccupant[]  $models
     * @return PropertyOccupant[]
     */
    public static function toDomainArray(array $models): array
    {
        return array_map(fn (EloquentPropertyOccupant $m) => self::toDomain($m), $models);
    }

    /** @return array<string, mixed> */
    public static function toPersistence(PropertyOccupant $occupant): array
    {
        return [
            'id' => $occupant->id(),
            'property_id' => $occupant->propertyId(),
            'contact_id' => $occupant->contactId(),
            'occupant_type_id' => $occupant->occupantTypeId(),
            'is_primary' => $occupant->isPrimary(),
            'move_in_date' => $occupant->moveInDate(),
            'move_out_date' => $occupant->moveOutDate(),
            'is_active' => $occupant->isActive(),
        ];
    }
}
