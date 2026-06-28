<?php

declare(strict_types=1);

namespace Urbania\Propiedades\Infrastructure\Mappers;

use App\Models\PropertyStatusLog as PropertyStatusLogModel;
use Urbania\Propiedades\Domain\Entities\PropertyStatusLogEntry;
use Urbania\Shared\Domain\ValueObjects\Uuid;

final readonly class PropertyStatusLogMapper
{
    public function toDomain(PropertyStatusLogModel $model): PropertyStatusLogEntry
    {
        return PropertyStatusLogEntry::reconstitute(
            id: Uuid::fromString($model->id),
            propertyId: Uuid::fromString($model->property_id),
            fromStatusId: $model->from_status_id === null ? null : Uuid::fromString($model->from_status_id),
            toStatusId: Uuid::fromString($model->to_status_id),
            changedByUserId: Uuid::fromString($model->changed_by_user_id),
            reason: $model->reason,
            createdAt: $this->toDateTimeImmutable($model->created_at),
        );
    }

    /**
     * @return array<string, mixed>
     */
    public function toPersistence(PropertyStatusLogEntry $entity): array
    {
        return [
            'id' => $entity->id()->toString(),
            'property_id' => $entity->propertyId()->toString(),
            'from_status_id' => $entity->fromStatusId()?->toString(),
            'to_status_id' => $entity->toStatusId()->toString(),
            'changed_by_user_id' => $entity->changedByUserId()->toString(),
            'reason' => $entity->reason(),
            'created_at' => $entity->createdAt()->format('Y-m-d H:i:s'),
        ];
    }

    /**
     * @param  array<PropertyStatusLogModel>  $models
     * @return array<PropertyStatusLogEntry>
     */
    public function toDomainArray(array $models): array
    {
        return array_map(fn (PropertyStatusLogModel $model): PropertyStatusLogEntry => $this->toDomain($model), $models);
    }

    private function toDateTimeImmutable(mixed $value): \DateTimeImmutable
    {
        assert($value instanceof \DateTimeInterface);

        return \DateTimeImmutable::createFromMutable($value instanceof \DateTime ? $value : \DateTime::createFromImmutable($value));
    }
}
