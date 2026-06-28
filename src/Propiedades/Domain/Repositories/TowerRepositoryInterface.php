<?php

declare(strict_types=1);

namespace Urbania\Propiedades\Domain\Repositories;

use Urbania\Propiedades\Domain\Entities\TowerEntity;
use Urbania\Shared\Domain\ValueObjects\Uuid;

interface TowerRepositoryInterface
{
    public function findById(Uuid $id): ?TowerEntity;

    /**
     * @param  array<string, mixed>  $filters
     * @return array{items: array<TowerEntity>, total: int, page: int, perPage: int, lastPage: int}
     */
    public function findByCondominiumId(Uuid $condominiumId, array $filters = [], int $page = 1, int $perPage = 20): array;

    /**
     * @param  array<string, mixed>  $filters
     * @return array{items: array<TowerEntity>, total: int, page: int, perPage: int, lastPage: int}
     */
    public function findAll(array $filters = [], int $page = 1, int $perPage = 20): array;

    public function save(TowerEntity $entity): void;

    public function delete(Uuid $id): void;

    public function existsByNameInCondominium(string $name, Uuid $condominiumId, ?Uuid $excludeId = null): bool;

    public function countByCondominiumId(Uuid $condominiumId): int;
}
