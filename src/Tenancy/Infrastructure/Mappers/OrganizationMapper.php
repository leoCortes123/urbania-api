<?php

declare(strict_types=1);

namespace Urbania\Tenancy\Infrastructure\Mappers;

use App\Models\Organization as OrganizationModel;
use Urbania\Shared\Domain\ValueObjects\Uuid;
use Urbania\Tenancy\Domain\Entities\OrganizationEntity;

final readonly class OrganizationMapper
{
    public function toDomain(OrganizationModel $model): OrganizationEntity
    {
        return new OrganizationEntity(
            id: Uuid::fromString($model->id),
            name: $model->name,
            type: $model->type,
            nit: $model->nit,
            country: $model->country,
            currency: $model->currency,
            status: $model->status,
        );
    }

    /**
     * @return array<string, mixed>
     */
    public function toPersistence(OrganizationEntity $entity): array
    {
        return [
            'id' => $entity->id()->toString(),
            'name' => $entity->name(),
            'type' => $entity->type(),
            'nit' => $entity->nit(),
            'email' => null,
            'country' => $entity->country(),
            'currency' => $entity->currency(),
            'status' => $entity->status(),
            'logo_url' => null,
        ];
    }
}
