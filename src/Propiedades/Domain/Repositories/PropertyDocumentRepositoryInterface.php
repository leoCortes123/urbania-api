<?php

declare(strict_types=1);

namespace Urbania\Propiedades\Domain\Repositories;

use Urbania\Propiedades\Domain\Entities\PropertyDocumentEntity;
use Urbania\Shared\Domain\ValueObjects\Uuid;

interface PropertyDocumentRepositoryInterface
{
    public function findById(Uuid $id): ?PropertyDocumentEntity;

    /**
     * @return array{items: array<PropertyDocumentEntity>, total: int, page: int, perPage: int, lastPage: int}
     */
    public function findByPropertyId(Uuid $propertyId, int $page = 1, int $perPage = 20): array;

    public function save(PropertyDocumentEntity $entity): void;

    public function delete(Uuid $id): void;

    public function countByPropertyId(Uuid $propertyId): int;
}
