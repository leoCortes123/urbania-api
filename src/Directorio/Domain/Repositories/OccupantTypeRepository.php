<?php

declare(strict_types=1);

namespace Directorio\Domain\Repositories;

use Directorio\Domain\Entities\OccupantType;

interface OccupantTypeRepository
{
    /** @return OccupantType[] */
    public function findAll(): array;

    public function findById(string $id): ?OccupantType;

    public function findByCode(string $code): ?OccupantType;
}
