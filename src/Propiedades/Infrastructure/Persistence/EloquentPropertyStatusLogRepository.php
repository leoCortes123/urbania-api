<?php

declare(strict_types=1);

namespace Urbania\Propiedades\Infrastructure\Persistence;

use App\Models\PropertyStatusLog as PropertyStatusLogModel;
use Urbania\Propiedades\Domain\Entities\PropertyStatusLogEntry;
use Urbania\Propiedades\Domain\Repositories\PropertyStatusLogRepositoryInterface;
use Urbania\Propiedades\Infrastructure\Mappers\PropertyStatusLogMapper;
use Urbania\Shared\Domain\ValueObjects\Uuid;

final readonly class EloquentPropertyStatusLogRepository implements PropertyStatusLogRepositoryInterface
{
    public function __construct(
        private PropertyStatusLogMapper $mapper,
    ) {}

    /**
     * @return array{items: array<PropertyStatusLogEntry>, total: int, page: int, perPage: int, lastPage: int}
     */
    public function findByPropertyId(Uuid $propertyId, int $page = 1, int $perPage = 20): array
    {
        $query = PropertyStatusLogModel::where('property_id', $propertyId->toString())
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

    public function save(PropertyStatusLogEntry $entity): void
    {
        $data = $this->mapper->toPersistence($entity);
        PropertyStatusLogModel::updateOrCreate(['id' => $entity->id()->toString()], $data);
    }
}
