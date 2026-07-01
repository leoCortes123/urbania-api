<?php

declare(strict_types=1);

namespace Urbania\Comunicaciones\Infrastructure\Mappers;

use App\Models\AnnouncementDelivery;
use Carbon\Carbon;
use Urbania\Comunicaciones\Domain\Entities\AnnouncementDeliveryEntity;
use Urbania\Comunicaciones\Domain\ValueObjects\DeliveryChannel;
use Urbania\Comunicaciones\Domain\ValueObjects\DeliveryStatus;
use Urbania\Shared\Domain\ValueObjects\Uuid;

final readonly class AnnouncementDeliveryMapper
{
    public function toDomain(AnnouncementDelivery $model): AnnouncementDeliveryEntity
    {
        /** @var array<string, mixed>|null $metadata */
        $metadata = $model->metadata;

        return AnnouncementDeliveryEntity::reconstitute(
            id: Uuid::fromString($model->id),
            announcementId: Uuid::fromString($model->announcement_id),
            contactId: Uuid::fromString($model->contact_id),
            canal: DeliveryChannel::fromString($model->canal),
            estado: DeliveryStatus::fromString($model->estado),
            externalId: $model->external_id,
            metadata: $metadata,
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
    public function toPersistence(AnnouncementDeliveryEntity $entity): array
    {
        return [
            'id' => $entity->id()->toString(),
            'announcement_id' => $entity->announcementId()->toString(),
            'contact_id' => $entity->contactId()->toString(),
            'canal' => $entity->canal()->value,
            'estado' => $entity->estado()->value,
            'external_id' => $entity->externalId(),
            'metadata' => $entity->metadata(),
        ];
    }
}
