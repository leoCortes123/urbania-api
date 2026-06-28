<?php

declare(strict_types=1);

namespace Urbania\Propiedades\Infrastructure\Mappers;

use App\Models\Tower as TowerModel;
use Urbania\Propiedades\Domain\Entities\TowerEntity;
use Urbania\Shared\Domain\ValueObjects\Uuid;

final readonly class TowerMapper
{
    public function toDomain(TowerModel $model): TowerEntity
    {
        return TowerEntity::reconstitute(
            id: Uuid::fromString($model->id),
            condominiumId: Uuid::fromString($model->condominium_id),
            name: $model->name,
            code: $model->code,
            floorCount: (int) $model->floor_count,
            hasElevator: (bool) $model->has_elevator,
            description: $model->description,
            sortOrder: (int) $model->sort_order,
            createdAt: $this->toDateTimeImmutable($model->created_at),
            updatedAt: $this->toDateTimeImmutable($model->updated_at),
            deletedAt: $model->deleted_at === null ? null : $this->toDateTimeImmutable($model->deleted_at),
        );
    }

    /**
     * @return array<string, mixed>
     */
    public function toPersistence(TowerEntity $entity): array
    {
        return [
            'id' => $entity->id()->toString(),
            'condominium_id' => $entity->condominiumId()->toString(),
            'name' => $entity->name(),
            'code' => $entity->code(),
            'floor_count' => $entity->floorCount(),
            'has_elevator' => $entity->hasElevator(),
            'description' => $entity->description(),
            'sort_order' => $entity->sortOrder(),
            'created_at' => $entity->createdAt()->format('Y-m-d H:i:s'),
            'updated_at' => $entity->updatedAt()->format('Y-m-d H:i:s'),
        ];
    }

    /**
     * @param  array<TowerModel>  $models
     * @return array<TowerEntity>
     */
    public function toDomainArray(array $models): array
    {
        return array_map(fn (TowerModel $model): TowerEntity => $this->toDomain($model), $models);
    }

    private function toDateTimeImmutable(mixed $value): \DateTimeImmutable
    {
        assert($value instanceof \DateTimeInterface);

        return \DateTimeImmutable::createFromMutable($value instanceof \DateTime ? $value : \DateTime::createFromImmutable($value));
    }
}
