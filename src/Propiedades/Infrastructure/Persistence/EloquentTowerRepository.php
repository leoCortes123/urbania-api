<?php

declare(strict_types=1);

namespace Urbania\Propiedades\Infrastructure\Persistence;

use App\Models\Tower as TowerModel;
use Urbania\Propiedades\Domain\Entities\TowerEntity;
use Urbania\Propiedades\Domain\Repositories\TowerRepositoryInterface;
use Urbania\Propiedades\Infrastructure\Mappers\TowerMapper;
use Urbania\Shared\Domain\ValueObjects\Uuid;

final readonly class EloquentTowerRepository implements TowerRepositoryInterface
{
    public function __construct(
        private TowerMapper $mapper,
    ) {}

    public function findById(Uuid $id): ?TowerEntity
    {
        $model = TowerModel::find($id->toString());

        return $model === null ? null : $this->mapper->toDomain($model);
    }

    /**
     * @param  array<string, mixed>  $filters
     * @return array{items: array<TowerEntity>, total: int, page: int, perPage: int, lastPage: int}
     */
    public function findByCondominiumId(Uuid $condominiumId, array $filters = [], int $page = 1, int $perPage = 20): array
    {
        $query = TowerModel::where('condominium_id', $condominiumId->toString());

        if (isset($filters['search']) && is_string($filters['search']) && $filters['search'] !== '') {
            $search = $filters['search'];
            $query->where(function ($q) use ($search): void {
                $q->where('name', 'ilike', "%{$search}%")
                    ->orWhere('code', 'ilike', "%{$search}%");
            });
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

    /**
     * @param  array<string, mixed>  $filters
     * @return array{items: array<TowerEntity>, total: int, page: int, perPage: int, lastPage: int}
     */
    public function findAll(array $filters = [], int $page = 1, int $perPage = 20): array
    {
        $query = TowerModel::query();

        if (isset($filters['search']) && is_string($filters['search']) && $filters['search'] !== '') {
            $search = $filters['search'];
            $query->where(function ($q) use ($search): void {
                $q->where('name', 'ilike', "%{$search}%")
                    ->orWhere('code', 'ilike', "%{$search}%");
            });
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

    public function save(TowerEntity $entity): void
    {
        $data = $this->mapper->toPersistence($entity);
        TowerModel::updateOrCreate(['id' => $entity->id()->toString()], $data);
    }

    public function delete(Uuid $id): void
    {
        TowerModel::where('id', $id->toString())->delete();
    }

    public function existsByNameInCondominium(string $name, Uuid $condominiumId, ?Uuid $excludeId = null): bool
    {
        $query = TowerModel::where('condominium_id', $condominiumId->toString())
            ->where('name', $name);

        if ($excludeId !== null) {
            $query->where('id', '!=', $excludeId->toString());
        }

        return $query->exists();
    }

    public function countByCondominiumId(Uuid $condominiumId): int
    {
        return TowerModel::where('condominium_id', $condominiumId->toString())->count();
    }
}
