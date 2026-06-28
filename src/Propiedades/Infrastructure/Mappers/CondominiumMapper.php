<?php

declare(strict_types=1);

namespace Urbania\Propiedades\Infrastructure\Mappers;

use App\Models\Condominium as CondominiumModel;
use Urbania\Propiedades\Domain\Entities\CondominiumEntity;
use Urbania\Shared\Domain\ValueObjects\Uuid;

final readonly class CondominiumMapper
{
    public function toDomain(CondominiumModel $model): CondominiumEntity
    {
        return CondominiumEntity::reconstitute(
            id: Uuid::fromString($model->id),
            name: $model->name,
            address: $model->address,
            city: $model->city,
            department: $model->department,
            country: $model->country,
            nit: $model->nit,
            phone: $model->phone,
            email: $model->email,
            legalRepresentative: $model->legal_representative,
            totalCoefficient: (string) $model->total_coefficient,
            logoUrl: $model->logo_url,
            isActive: (bool) $model->is_active,
            createdAt: $this->toDateTimeImmutable($model->created_at),
            updatedAt: $this->toDateTimeImmutable($model->updated_at),
            deletedAt: $model->deleted_at === null ? null : $this->toDateTimeImmutable($model->deleted_at),
        );
    }

    /**
     * @return array<string, mixed>
     */
    public function toPersistence(CondominiumEntity $entity): array
    {
        return [
            'id' => $entity->id()->toString(),
            'name' => $entity->name(),
            'address' => $entity->address(),
            'city' => $entity->city(),
            'department' => $entity->department(),
            'country' => $entity->country(),
            'nit' => $entity->nit(),
            'phone' => $entity->phone(),
            'email' => $entity->email(),
            'legal_representative' => $entity->legalRepresentative(),
            'total_coefficient' => $entity->totalCoefficient(),
            'logo_url' => $entity->logoUrl(),
            'is_active' => $entity->isActive(),
            'created_at' => $entity->createdAt()->format('Y-m-d H:i:s'),
            'updated_at' => $entity->updatedAt()->format('Y-m-d H:i:s'),
        ];
    }

    /**
     * @param  array<CondominiumModel>  $models
     * @return array<CondominiumEntity>
     */
    public function toDomainArray(array $models): array
    {
        return array_map(fn (CondominiumModel $model): CondominiumEntity => $this->toDomain($model), $models);
    }

    private function toDateTimeImmutable(mixed $value): \DateTimeImmutable
    {
        assert($value instanceof \DateTimeInterface);

        return \DateTimeImmutable::createFromMutable($value instanceof \DateTime ? $value : \DateTime::createFromImmutable($value));
    }
}
