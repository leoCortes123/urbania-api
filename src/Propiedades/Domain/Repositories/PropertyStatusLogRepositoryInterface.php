<?php

declare(strict_types=1);

namespace Urbania\Propiedades\Domain\Repositories;

use Urbania\Propiedades\Domain\Entities\PropertyStatusLogEntry;
use Urbania\Shared\Domain\ValueObjects\Uuid;

interface PropertyStatusLogRepositoryInterface
{
    /**
     * @return array{items: array<PropertyStatusLogEntry>, total: int, page: int, perPage: int, lastPage: int}
     */
    public function findByPropertyId(Uuid $propertyId, int $page = 1, int $perPage = 20): array;

    public function save(PropertyStatusLogEntry $entity): void;
}
