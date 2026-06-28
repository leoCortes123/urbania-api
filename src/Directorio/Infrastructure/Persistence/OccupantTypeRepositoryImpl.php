<?php

declare(strict_types=1);

namespace Directorio\Infrastructure\Persistence;

use App\Models\OccupantType as EloquentOccupantType;
use Directorio\Domain\Entities\OccupantType;
use Directorio\Domain\Repositories\OccupantTypeRepository;
use Directorio\Infrastructure\Mappers\OccupantTypeMapper;

class OccupantTypeRepositoryImpl implements OccupantTypeRepository
{
    public function findAll(): array
    {
        $models = EloquentOccupantType::where('is_active', true)
            ->orderBy('sort_order')
            ->get();

        return OccupantTypeMapper::toDomainArray($models->all());
    }

    public function findById(string $id): ?OccupantType
    {
        $model = EloquentOccupantType::find($id);

        return $model ? OccupantTypeMapper::toDomain($model) : null;
    }

    public function findByCode(string $code): ?OccupantType
    {
        $model = EloquentOccupantType::where('code', $code)->first();

        return $model ? OccupantTypeMapper::toDomain($model) : null;
    }
}
