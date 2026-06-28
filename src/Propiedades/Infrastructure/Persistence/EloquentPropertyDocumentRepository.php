<?php

declare(strict_types=1);

namespace Urbania\Propiedades\Infrastructure\Persistence;

use App\Models\PropertyDocument as PropertyDocumentModel;
use Urbania\Propiedades\Domain\Entities\PropertyDocumentEntity;
use Urbania\Propiedades\Domain\Repositories\PropertyDocumentRepositoryInterface;
use Urbania\Propiedades\Infrastructure\Mappers\PropertyDocumentMapper;
use Urbania\Shared\Domain\ValueObjects\Uuid;

final readonly class EloquentPropertyDocumentRepository implements PropertyDocumentRepositoryInterface
{
    public function __construct(
        private PropertyDocumentMapper $mapper,
    ) {}

    public function findById(Uuid $id): ?PropertyDocumentEntity
    {
        $model = PropertyDocumentModel::find($id->toString());

        return $model === null ? null : $this->mapper->toDomain($model);
    }

    /**
     * @return array{items: array<PropertyDocumentEntity>, total: int, page: int, perPage: int, lastPage: int}
     */
    public function findByPropertyId(Uuid $propertyId, int $page = 1, int $perPage = 20): array
    {
        $query = PropertyDocumentModel::where('property_id', $propertyId->toString())
            ->orderBy('created_at', 'desc');

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

    public function save(PropertyDocumentEntity $entity): void
    {
        $data = $this->mapper->toPersistence($entity);
        PropertyDocumentModel::updateOrCreate(['id' => $entity->id()->toString()], $data);
    }

    public function delete(Uuid $id): void
    {
        PropertyDocumentModel::where('id', $id->toString())->delete();
    }

    public function countByPropertyId(Uuid $propertyId): int
    {
        return PropertyDocumentModel::where('property_id', $propertyId->toString())->count();
    }
}
