<?php

declare(strict_types=1);

namespace Urbania\Comunicaciones\Infrastructure\Mappers;

use App\Models\CommunicationChannel;
use Carbon\Carbon;
use Urbania\Comunicaciones\Domain\Entities\CommunicationChannelEntity;
use Urbania\Comunicaciones\Domain\ValueObjects\DeliveryChannel;
use Urbania\Shared\Domain\ValueObjects\Uuid;

final readonly class CommunicationChannelMapper
{
    public function toDomain(CommunicationChannel $model): CommunicationChannelEntity
    {
        /** @var array<string, mixed>|null $config */
        $config = $model->config;

        return CommunicationChannelEntity::reconstitute(
            id: Uuid::fromString($model->id),
            condominiumId: Uuid::fromString($model->condominium_id),
            canal: DeliveryChannel::fromString($model->canal),
            provider: $model->provider,
            config: $config,
            activo: $model->activo,
            createdAt: $this->toDateTimeImmutable($model->created_at),
            updatedAt: $this->toDateTimeImmutable($model->updated_at),
        );
    }

    private function toDateTimeImmutable(?Carbon $carbon): \DateTimeImmutable
    {
        return $carbon?->toDateTimeImmutable() ?? new \DateTimeImmutable;
    }

    /**
     * @return array<string, mixed>
     */
    public function toPersistence(CommunicationChannelEntity $entity): array
    {
        return [
            'id' => $entity->id()->toString(),
            'condominium_id' => $entity->condominiumId()->toString(),
            'canal' => $entity->canal()->value,
            'provider' => $entity->provider(),
            'config' => $entity->config(),
            'activo' => $entity->activo(),
        ];
    }
}
