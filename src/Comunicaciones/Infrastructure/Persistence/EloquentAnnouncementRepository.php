<?php

declare(strict_types=1);

namespace Urbania\Comunicaciones\Infrastructure\Persistence;

use App\Models\Announcement as AnnouncementModel;
use Urbania\Comunicaciones\Domain\Entities\AnnouncementEntity;
use Urbania\Comunicaciones\Domain\Repositories\AnnouncementRepositoryInterface;
use Urbania\Comunicaciones\Infrastructure\Mappers\AnnouncementMapper;
use Urbania\Shared\Domain\ValueObjects\Uuid;

final readonly class EloquentAnnouncementRepository implements AnnouncementRepositoryInterface
{
    public function __construct(
        private AnnouncementMapper $mapper,
    ) {}

    public function findById(Uuid $id): ?AnnouncementEntity
    {
        $model = AnnouncementModel::find($id->toString());

        return $model !== null ? $this->mapper->toDomain($model) : null;
    }

    /**
     * @param  array<string, mixed>  $filters
     * @return array{items: array<AnnouncementEntity>, total: int, page: int, perPage: int, lastPage: int}
     */
    public function findByCondominiumId(Uuid $condominiumId, array $filters = [], int $page = 1, int $perPage = 20): array
    {
        $query = AnnouncementModel::forCondominium($condominiumId->toString());

        if (! empty($filters['estado']) && is_string($filters['estado'])) {
            $query->where('estado', $filters['estado']);
        }

        if (! empty($filters['segmento']) && is_string($filters['segmento'])) {
            $query->where('segmento', $filters['segmento']);
        }

        $paginator = $query->orderByDesc('created_at')->paginate($perPage, ['*'], 'page', $page);

        /** @var array<AnnouncementEntity> $items */
        $items = array_map(
            fn (AnnouncementModel $model) => $this->mapper->toDomain($model),
            $paginator->items(),
        );

        return [
            'items' => $items,
            'total' => $paginator->total(),
            'page' => $paginator->currentPage(),
            'perPage' => $paginator->perPage(),
            'lastPage' => $paginator->lastPage(),
        ];
    }

    public function save(AnnouncementEntity $entity): void
    {
        $data = $this->mapper->toPersistence($entity);

        AnnouncementModel::updateOrCreate(
            ['id' => $entity->id()->toString()],
            $data,
        );
    }

    public function delete(Uuid $id): void
    {
        AnnouncementModel::where('id', $id->toString())->delete();
    }
}
