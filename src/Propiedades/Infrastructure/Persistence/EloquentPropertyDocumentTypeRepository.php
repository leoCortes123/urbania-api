<?php

declare(strict_types=1);

namespace Urbania\Propiedades\Infrastructure\Persistence;

use App\Models\PropertyDocumentType as PropertyDocumentTypeModel;
use Urbania\Propiedades\Domain\Entities\PropertyDocumentTypeEntity;
use Urbania\Propiedades\Domain\Repositories\PropertyDocumentTypeRepositoryInterface;
use Urbania\Propiedades\Infrastructure\Mappers\PropertyDocumentTypeMapper;
use Urbania\Shared\Domain\ValueObjects\Uuid;

final readonly class EloquentPropertyDocumentTypeRepository implements PropertyDocumentTypeRepositoryInterface
{
    public function __construct(
        private PropertyDocumentTypeMapper $mapper,
    ) {}

    public function findById(Uuid $id): ?PropertyDocumentTypeEntity
    {
        $model = PropertyDocumentTypeModel::find($id->toString());

        return $model === null ? null : $this->mapper->toDomain($model);
    }

    public function findByCode(string $code): ?PropertyDocumentTypeEntity
    {
        $model = PropertyDocumentTypeModel::where('code', $code)->first();

        return $model === null ? null : $this->mapper->toDomain($model);
    }

    /**
     * @param  array<string, mixed>  $filters
     * @return array{items: array<PropertyDocumentTypeEntity>, total: int, page: int, perPage: int, lastPage: int}
     */
    public function findAll(array $filters = [], int $page = 1, int $perPage = 20): array
    {
        $query = PropertyDocumentTypeModel::query();

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

    public function save(PropertyDocumentTypeEntity $entity): void
    {
        $data = $this->mapper->toPersistence($entity);
        PropertyDocumentTypeModel::updateOrCreate(['id' => $entity->id()->toString()], $data);
    }

    public function delete(Uuid $id): void
    {
        PropertyDocumentTypeModel::where('id', $id->toString())->delete();
    }

    public function existsByCode(string $code, ?Uuid $excludeId = null): bool
    {
        $query = PropertyDocumentTypeModel::where('code', $code);

        if ($excludeId !== null) {
            $query->where('id', '!=', $excludeId->toString());
        }

        return $query->exists();
    }

    public function hasActiveDocuments(Uuid $id): bool
    {
        $model = PropertyDocumentTypeModel::find($id->toString());

        return $model !== null && $model->documents()->exists();
    }
}
