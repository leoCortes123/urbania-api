<?php

declare(strict_types=1);

namespace Urbania\Comunicaciones\Infrastructure\Persistence;

use App\Models\MessageTemplate as MessageTemplateModel;
use Urbania\Comunicaciones\Domain\Entities\MessageTemplateEntity;
use Urbania\Comunicaciones\Domain\Repositories\MessageTemplateRepositoryInterface;
use Urbania\Comunicaciones\Infrastructure\Mappers\MessageTemplateMapper;
use Urbania\Shared\Domain\ValueObjects\Uuid;

final readonly class EloquentMessageTemplateRepository implements MessageTemplateRepositoryInterface
{
    public function __construct(
        private MessageTemplateMapper $mapper,
    ) {}

    public function findById(Uuid $id): ?MessageTemplateEntity
    {
        $model = MessageTemplateModel::withTrashed()->find($id->toString());

        return $model !== null ? $this->mapper->toDomain($model) : null;
    }

    /**
     * @param  array<string, mixed>  $filters
     * @return array{items: array<MessageTemplateEntity>, total: int, page: int, perPage: int, lastPage: int}
     */
    public function findByCondominiumId(Uuid $condominiumId, array $filters = [], int $page = 1, int $perPage = 20): array
    {
        $query = MessageTemplateModel::forCondominium($condominiumId->toString());

        if (! empty($filters['tipo']) && is_string($filters['tipo'])) {
            $query->where('tipo', $filters['tipo']);
        }

        if (! empty($filters['nombre']) && is_string($filters['nombre'])) {
            $query->where('nombre', 'ilike', '%'.$filters['nombre'].'%');
        }

        $paginator = $query->orderBy('nombre')->paginate($perPage, ['*'], 'page', $page);

        /** @var array<MessageTemplateEntity> $items */
        $items = array_map(
            fn (MessageTemplateModel $model) => $this->mapper->toDomain($model),
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

    public function save(MessageTemplateEntity $entity): void
    {
        $data = $this->mapper->toPersistence($entity);

        MessageTemplateModel::updateOrCreate(
            ['id' => $entity->id()->toString()],
            $data,
        );
    }

    public function delete(Uuid $id): void
    {
        MessageTemplateModel::where('id', $id->toString())->delete();
    }
}
