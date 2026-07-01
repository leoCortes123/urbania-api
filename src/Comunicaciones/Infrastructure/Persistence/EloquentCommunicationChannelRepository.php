<?php

declare(strict_types=1);

namespace Urbania\Comunicaciones\Infrastructure\Persistence;

use App\Models\CommunicationChannel as CommunicationChannelModel;
use Urbania\Comunicaciones\Domain\Entities\CommunicationChannelEntity;
use Urbania\Comunicaciones\Domain\Repositories\CommunicationChannelRepositoryInterface;
use Urbania\Comunicaciones\Domain\ValueObjects\DeliveryChannel;
use Urbania\Comunicaciones\Infrastructure\Mappers\CommunicationChannelMapper;
use Urbania\Shared\Domain\ValueObjects\Uuid;

final readonly class EloquentCommunicationChannelRepository implements CommunicationChannelRepositoryInterface
{
    public function __construct(
        private CommunicationChannelMapper $mapper,
    ) {}

    public function findById(Uuid $id): ?CommunicationChannelEntity
    {
        $model = CommunicationChannelModel::find($id->toString());

        return $model !== null ? $this->mapper->toDomain($model) : null;
    }

    public function findByCondominiumAndChannel(Uuid $condominiumId, DeliveryChannel $canal): ?CommunicationChannelEntity
    {
        $model = CommunicationChannelModel::where('condominium_id', $condominiumId->toString())
            ->where('canal', $canal->value)
            ->first();

        return $model !== null ? $this->mapper->toDomain($model) : null;
    }

    /**
     * @return array<CommunicationChannelEntity>
     */
    public function findByCondominiumId(Uuid $condominiumId): array
    {
        $models = CommunicationChannelModel::forCondominium($condominiumId->toString())->get();

        return $models->map(fn (CommunicationChannelModel $m) => $this->mapper->toDomain($m))->all();
    }

    public function save(CommunicationChannelEntity $entity): void
    {
        $data = $this->mapper->toPersistence($entity);

        CommunicationChannelModel::updateOrCreate(
            ['id' => $entity->id()->toString()],
            $data,
        );
    }
}
