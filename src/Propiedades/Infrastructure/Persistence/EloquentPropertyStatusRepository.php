<?php

declare(strict_types=1);

namespace Urbania\Propiedades\Infrastructure\Persistence;

use App\Models\PropertyStatus as PropertyStatusModel;
use Urbania\Propiedades\Domain\Entities\PropertyStatusEntity;
use Urbania\Propiedades\Domain\Repositories\PropertyStatusRepositoryInterface;
use Urbania\Propiedades\Infrastructure\Mappers\PropertyStatusMapper;
use Urbania\Shared\Domain\ValueObjects\Uuid;

final readonly class EloquentPropertyStatusRepository implements PropertyStatusRepositoryInterface
{
    public function __construct(
        private PropertyStatusMapper $mapper,
    ) {}

    public function findById(Uuid $id): ?PropertyStatusEntity
    {
        $model = PropertyStatusModel::find($id->toString());

        return $model === null ? null : $this->mapper->toDomain($model);
    }

    public function findByCode(string $code): ?PropertyStatusEntity
    {
        $model = PropertyStatusModel::where('code', $code)->first();

        return $model === null ? null : $this->mapper->toDomain($model);
    }

    /**
     * @param  array<string, mixed>  $filters
     * @return array{items: array<PropertyStatusEntity>, total: int, page: int, perPage: int, lastPage: int}
     */
    public function findAll(array $filters = [], int $page = 1, int $perPage = 20): array
    {
        $query = PropertyStatusModel::query();

        if (isset($filters['search']) && is_string($filters['search']) && $filters['search'] !== '') {
            $search = $filters['search'];
            $query->where(function ($q) use ($search): void {
                $q->where('code', 'ilike', "%{$search}%")
                    ->orWhere('name', 'ilike', "%{$search}%");
            });
        }

        if (isset($filters['is_active'])) {
            $isActive = $filters['is_active'];
            $query->where('is_active', is_bool($isActive) ? $isActive : filter_var($isActive, FILTER_VALIDATE_BOOLEAN));
        }

        $sortBy = is_string($filters['sort_by'] ?? null) && $filters['sort_by'] !== ''
            ? $filters['sort_by']
            : 'sort_order';
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

    public function save(PropertyStatusEntity $entity): void
    {
        $data = $this->mapper->toPersistence($entity);
        PropertyStatusModel::updateOrCreate(['id' => $entity->id()->toString()], $data);
    }

    public function delete(Uuid $id): void
    {
        PropertyStatusModel::where('id', $id->toString())->delete();
    }

    public function existsByCode(string $code, ?Uuid $excludeId = null): bool
    {
        $query = PropertyStatusModel::where('code', $code);

        if ($excludeId !== null) {
            $query->where('id', '!=', $excludeId->toString());
        }

        return $query->exists();
    }

    public function hasActiveProperties(Uuid $id): bool
    {
        $model = PropertyStatusModel::find($id->toString());

        return $model !== null && $model->properties()->exists();
    }
}
