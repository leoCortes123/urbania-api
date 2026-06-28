<?php

declare(strict_types=1);

namespace Directorio\Infrastructure\Persistence;

use App\Models\PropertyOccupant as EloquentPropertyOccupant;
use Directorio\Domain\Entities\PropertyOccupant;
use Directorio\Domain\Repositories\PropertyOccupantRepository;
use Directorio\Infrastructure\Mappers\PropertyOccupantMapper;

class PropertyOccupantRepositoryImpl implements PropertyOccupantRepository
{
    public function findByProperty(string $propertyId): array
    {
        $models = EloquentPropertyOccupant::where('property_id', $propertyId)
            ->with(['contact', 'occupantType'])
            ->orderBy('created_at')
            ->get();

        return PropertyOccupantMapper::toDomainArray($models->all());
    }

    public function findById(string $id): ?PropertyOccupant
    {
        $model = EloquentPropertyOccupant::find($id);

        return $model ? PropertyOccupantMapper::toDomain($model) : null;
    }

    public function findByContact(string $contactId): array
    {
        $models = EloquentPropertyOccupant::where('contact_id', $contactId)
            ->with(['contact', 'occupantType'])
            ->get();

        return PropertyOccupantMapper::toDomainArray($models->all());
    }

    public function findActiveByPropertyAndType(string $propertyId, string $occupantTypeId): array
    {
        $models = EloquentPropertyOccupant::where('property_id', $propertyId)
            ->where('occupant_type_id', $occupantTypeId)
            ->where('is_active', true)
            ->whereNull('deleted_at')
            ->get();

        return PropertyOccupantMapper::toDomainArray($models->all());
    }

    public function save(PropertyOccupant $occupant): PropertyOccupant
    {
        $data = PropertyOccupantMapper::toPersistence($occupant);
        EloquentPropertyOccupant::create($data);

        return $occupant;
    }

    public function update(PropertyOccupant $occupant): PropertyOccupant
    {
        $data = PropertyOccupantMapper::toPersistence($occupant);
        EloquentPropertyOccupant::where('id', $occupant->id())->update($data);

        return $occupant;
    }

    public function delete(string $id): void
    {
        EloquentPropertyOccupant::where('id', $id)->delete();
    }

    public function countActiveOwnersByProperty(string $propertyId): int
    {
        return EloquentPropertyOccupant::where('property_id', $propertyId)
            ->where('is_active', true)
            ->whereNull('deleted_at')
            ->whereHas('occupantType', fn ($q) => $q->where('code', 'propietario'))
            ->count();
    }

    public function findActiveByContact(string $contactId): array
    {
        $models = EloquentPropertyOccupant::where('contact_id', $contactId)
            ->where('is_active', true)
            ->whereNull('deleted_at')
            ->get();

        return PropertyOccupantMapper::toDomainArray($models->all());
    }
}
