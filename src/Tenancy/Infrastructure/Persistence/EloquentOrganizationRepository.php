<?php

declare(strict_types=1);

namespace Urbania\Tenancy\Infrastructure\Persistence;

use App\Models\Organization as OrganizationModel;
use Urbania\Shared\Domain\ValueObjects\Uuid;
use Urbania\Tenancy\Domain\Repositories\OrganizationRepositoryInterface;
use Urbania\Tenancy\Infrastructure\Mappers\OrganizationMapper;

final readonly class EloquentOrganizationRepository implements OrganizationRepositoryInterface
{
    public function __construct(
        private OrganizationMapper $mapper,
    ) {}

    public function findById(Uuid $id): ?object
    {
        $model = OrganizationModel::find($id->toString());

        if ($model === null) {
            return null;
        }

        return $this->mapper->toDomain($model);
    }

    public function exists(string $id): bool
    {
        return OrganizationModel::where('id', $id)->exists();
    }
}
