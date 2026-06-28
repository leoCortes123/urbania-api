<?php

declare(strict_types=1);

namespace Directorio\Infrastructure\Mappers;

use App\Models\OccupantType as EloquentOccupantType;
use Directorio\Domain\Entities\OccupantType;
use Directorio\Domain\ValueObjects\OccupantTypeCode;

class OccupantTypeMapper
{
    public static function toDomain(EloquentOccupantType $model): OccupantType
    {
        return new OccupantType(
            id: $model->id,
            code: new OccupantTypeCode($model->code),
            name: $model->name,
            sortOrder: $model->sort_order,
            isActive: $model->is_active,
        );
    }

    /**
     * @param  EloquentOccupantType[]  $models
     * @return OccupantType[]
     */
    public static function toDomainArray(array $models): array
    {
        return array_map(fn (EloquentOccupantType $m) => self::toDomain($m), $models);
    }
}
