<?php

declare(strict_types=1);

namespace Urbania\Propiedades\Domain\Repositories;

use Urbania\Propiedades\Domain\Entities\PropertyEntity;
use Urbania\Shared\Domain\ValueObjects\Uuid;

interface PropertyRepositoryInterface
{
    public function findById(Uuid $id): ?PropertyEntity;

    /**
     * @param  array<string, mixed>  $filters
     * @return array{items: array<PropertyEntity>, total: int, page: int, perPage: int, lastPage: int}
     */
    public function findAll(array $filters = [], int $page = 1, int $perPage = 20): array;

    public function save(PropertyEntity $entity): void;

    public function delete(Uuid $id): void;

    public function existsByUnitNumber(Uuid $towerId, int $floor, string $unitNumber, ?Uuid $excludeId = null): bool;

    public function countByCondominium(Uuid $condominiumId): int;

    public function countByType(Uuid $typeId): int;

    public function countByStatus(Uuid $statusId): int;

    public function countByTower(Uuid $towerId): int;

    public function sumCoefficientsByCondominium(Uuid $condominiumId): float;

    /**
     * @return array<PropertyEntity>
     */
    public function findByCondominiumAndTower(Uuid $condominiumId, Uuid $towerId): array;

    public function hasActiveResidents(Uuid $propertyId): bool;

    public function hasPendingFees(Uuid $propertyId): bool;
}
