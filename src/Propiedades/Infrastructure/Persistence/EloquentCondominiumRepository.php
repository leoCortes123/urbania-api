<?php

declare(strict_types=1);

namespace Urbania\Propiedades\Infrastructure\Persistence;

use App\Models\Condominium as CondominiumModel;
use Urbania\Propiedades\Domain\Entities\CondominiumEntity;
use Urbania\Propiedades\Domain\Repositories\CondominiumRepositoryInterface;
use Urbania\Propiedades\Infrastructure\Mappers\CondominiumMapper;
use Urbania\Shared\Domain\ValueObjects\Uuid;

final readonly class EloquentCondominiumRepository implements CondominiumRepositoryInterface
{
    public function __construct(
        private CondominiumMapper $mapper,
    ) {}

    public function findById(Uuid $id): ?CondominiumEntity
    {
        $model = CondominiumModel::find($id->toString());

        return $model === null ? null : $this->mapper->toDomain($model);
    }

    /**
     * @param  array<string, mixed>  $filters
     * @return array{items: array<CondominiumEntity>, total: int, page: int, perPage: int, lastPage: int}
     */
    public function findAll(array $filters = [], int $page = 1, int $perPage = 20): array
    {
        $query = CondominiumModel::query();

        if (isset($filters['search']) && is_string($filters['search']) && $filters['search'] !== '') {
            $search = $filters['search'];
            $query->where(function ($q) use ($search): void {
                $q->where('name', 'ilike', "%{$search}%")
                    ->orWhere('city', 'ilike', "%{$search}%")
                    ->orWhere('nit', 'ilike', "%{$search}%");
            });
        }

        if (isset($filters['is_active'])) {
            $isActive = $filters['is_active'];
            $query->where('is_active', is_bool($isActive) ? $isActive : filter_var($isActive, FILTER_VALIDATE_BOOLEAN));
        }

        $sortBy = is_string($filters['sort_by'] ?? null) && $filters['sort_by'] !== ''
            ? $filters['sort_by']
            : 'name';
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

    public function save(CondominiumEntity $entity): void
    {
        $data = $this->mapper->toPersistence($entity);
        CondominiumModel::updateOrCreate(['id' => $entity->id()->toString()], $data);
    }

    public function delete(Uuid $id): void
    {
        CondominiumModel::where('id', $id->toString())->delete();
    }
}
