<?php

declare(strict_types=1);

namespace Urbania\Propiedades\Infrastructure\Persistence;

use App\Models\Property as PropertyModel;
use Urbania\Propiedades\Domain\Entities\PropertyEntity;
use Urbania\Propiedades\Domain\Repositories\PropertyRepositoryInterface;
use Urbania\Propiedades\Infrastructure\Mappers\PropertyMapper;
use Urbania\Shared\Domain\ValueObjects\Uuid;

final readonly class EloquentPropertyRepository implements PropertyRepositoryInterface
{
    public function __construct(
        private PropertyMapper $mapper,
    ) {}

    public function findById(Uuid $id): ?PropertyEntity
    {
        $model = PropertyModel::find($id->toString());

        return $model === null ? null : $this->mapper->toDomain($model);
    }

    /**
     * @param  array<string, mixed>  $filters
     * @return array{items: array<PropertyEntity>, total: int, page: int, perPage: int, lastPage: int}
     */
    public function findAll(array $filters = [], int $page = 1, int $perPage = 20): array
    {
        $query = PropertyModel::query();

        if (isset($filters['condominium_id']) && is_string($filters['condominium_id']) && $filters['condominium_id'] !== '') {
            $query->where('condominium_id', $filters['condominium_id']);
        }

        if (isset($filters['tower_id']) && is_string($filters['tower_id']) && $filters['tower_id'] !== '') {
            $query->where('tower_id', $filters['tower_id']);
        }

        if (isset($filters['property_type_id']) && is_string($filters['property_type_id']) && $filters['property_type_id'] !== '') {
            $query->where('property_type_id', $filters['property_type_id']);
        }

        if (isset($filters['property_status_id']) && is_string($filters['property_status_id']) && $filters['property_status_id'] !== '') {
            $query->where('property_status_id', $filters['property_status_id']);
        }

        if (isset($filters['floor']) && is_numeric($filters['floor'])) {
            $query->where('floor', (int) $filters['floor']);
        }

        if (isset($filters['floor_min']) && is_numeric($filters['floor_min'])) {
            $query->where('floor', '>=', (int) $filters['floor_min']);
        }

        if (isset($filters['floor_max']) && is_numeric($filters['floor_max'])) {
            $query->where('floor', '<=', (int) $filters['floor_max']);
        }

        if (isset($filters['is_active'])) {
            $isActive = $filters['is_active'];
            $query->where('is_active', is_bool($isActive) ? $isActive : filter_var($isActive, FILTER_VALIDATE_BOOLEAN));
        }

        if (isset($filters['search']) && is_string($filters['search']) && $filters['search'] !== '') {
            $search = $filters['search'];
            $query->where('unit_number', 'ilike', "%{$search}%");
        }

        $sortBy = is_string($filters['sort_by'] ?? null) && $filters['sort_by'] !== ''
            ? $filters['sort_by']
            : 'created_at';
        $sortOrder = is_string($filters['sort_order'] ?? null) && strtolower($filters['sort_order']) === 'desc'
            ? 'desc'
            : 'asc';

        $query->orderBy($sortBy, $sortOrder);

        $total = $query->count();
        $page = max(1, $page);
        $perPage = max(1, min(100, $perPage));
        $lastPage = (int) max(1, ceil($total / $perPage));

        $models = $query->offset(($page - 1) * $perPage)->limit($perPage)->get();

        return [
            'items' => $this->mapper->toDomainArray($models->all()),
            'total' => $total,
            'page' => $page,
            'perPage' => $perPage,
            'lastPage' => $lastPage,
        ];
    }

    public function save(PropertyEntity $entity): void
    {
        $data = $this->mapper->toPersistence($entity);
        PropertyModel::updateOrCreate(['id' => $entity->id()->toString()], $data);
    }

    public function delete(Uuid $id): void
    {
        PropertyModel::where('id', $id->toString())->delete();
    }

    public function existsByUnitNumber(Uuid $towerId, int $floor, string $unitNumber, ?Uuid $excludeId = null): bool
    {
        $query = PropertyModel::where('tower_id', $towerId->toString())
            ->where('floor', $floor)
            ->where('unit_number', $unitNumber);

        if ($excludeId !== null) {
            $query->where('id', '!=', $excludeId->toString());
        }

        return $query->exists();
    }

    public function countByCondominium(Uuid $condominiumId): int
    {
        return PropertyModel::where('condominium_id', $condominiumId->toString())->count();
    }

    public function countByType(Uuid $typeId): int
    {
        return PropertyModel::where('property_type_id', $typeId->toString())->count();
    }

    public function countByStatus(Uuid $statusId): int
    {
        return PropertyModel::where('property_status_id', $statusId->toString())->count();
    }

    public function countByTower(Uuid $towerId): int
    {
        return PropertyModel::where('tower_id', $towerId->toString())->count();
    }

    public function sumCoefficientsByCondominium(Uuid $condominiumId): float
    {
        return (float) PropertyModel::where('condominium_id', $condominiumId->toString())->sum('coefficient');
    }

    /**
     * @return array<PropertyEntity>
     */
    public function findByCondominiumAndTower(Uuid $condominiumId, Uuid $towerId): array
    {
        $models = PropertyModel::where('condominium_id', $condominiumId->toString())
            ->where('tower_id', $towerId->toString())
            ->get();

        return $this->mapper->toDomainArray($models->all());
    }

    public function hasActiveResidents(Uuid $propertyId): bool
    {
        return false;
    }

    public function hasPendingFees(Uuid $propertyId): bool
    {
        return false;
    }
}
