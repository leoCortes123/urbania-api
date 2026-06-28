<?php

declare(strict_types=1);

namespace Urbania\Propiedades\Domain\Repositories;

use Urbania\Propiedades\Domain\Entities\PropertyStatusEntity;
use Urbania\Shared\Domain\ValueObjects\Uuid;

interface PropertyStatusRepositoryInterface
{
    public function findById(Uuid $id): ?PropertyStatusEntity;

    public function findByCode(string $code): ?PropertyStatusEntity;

    /**
     * @param  array<string, mixed>  $filters
     * @return array{items: array<PropertyStatusEntity>, total: int, page: int, perPage: int, lastPage: int}
     */
    public function findAll(array $filters = [], int $page = 1, int $perPage = 20): array;

    public function save(PropertyStatusEntity $entity): void;

    public function delete(Uuid $id): void;

    public function existsByCode(string $code, ?Uuid $excludeId = null): bool;

    public function hasActiveProperties(Uuid $id): bool;
}
