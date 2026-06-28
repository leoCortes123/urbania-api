<?php

declare(strict_types=1);

namespace Urbania\Propiedades\Domain\Repositories;

use Urbania\Propiedades\Domain\Entities\CondominiumEntity;
use Urbania\Shared\Domain\ValueObjects\Uuid;

interface CondominiumRepositoryInterface
{
    public function findById(Uuid $id): ?CondominiumEntity;

    /**
     * @param  array<string, mixed>  $filters
     * @return array{items: array<CondominiumEntity>, total: int, page: int, perPage: int, lastPage: int}
     */
    public function findAll(array $filters = [], int $page = 1, int $perPage = 20): array;

    public function save(CondominiumEntity $entity): void;

    public function delete(Uuid $id): void;
}
