<?php

declare(strict_types=1);

namespace Directorio\Infrastructure\Persistence;

use App\Models\PropertyOccupant as EloquentPropertyOccupant;
use Directorio\Domain\Entities\PropertyOccupant;
use Directorio\Domain\Repositories\PropertyOccupantRepository;
use Directorio\Infrastructure\Mappers\PropertyOccupantMapper;
use Illuminate\Support\Facades\DB;

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

    public function findActiveByContact(string $contactId): array
    {
        $models = EloquentPropertyOccupant::where('contact_id', $contactId)
            ->where('is_active', true)
            ->whereNull('deleted_at')
            ->get();

        return PropertyOccupantMapper::toDomainArray($models->all());
    }

    public function findActivePortalPrimaryByPropertyId(string $propertyId): ?PropertyOccupant
    {
        $model = EloquentPropertyOccupant::where('property_id', $propertyId)
            ->where('is_portal_primary', true)
            ->where('is_active', true)
            ->whereNull('deleted_at')
            ->first();

        return $model ? PropertyOccupantMapper::toDomain($model) : null;
    }

    public function setPortalPrimary(string $propertyId, string $occupantId): void
    {
        DB::transaction(function () use ($propertyId, $occupantId): void {
            EloquentPropertyOccupant::where('property_id', $propertyId)
                ->where('is_portal_primary', true)
                ->whereNull('deleted_at')
                ->update(['is_portal_primary' => false]);

            EloquentPropertyOccupant::where('id', $occupantId)
                ->where('property_id', $propertyId)
                ->whereNull('deleted_at')
                ->update(['is_portal_primary' => true]);
        });
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
}
