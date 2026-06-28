<?php

declare(strict_types=1);

namespace Urbania\Propiedades\Domain\Repositories;

use Urbania\Propiedades\Domain\Entities\PropertyDocumentTypeEntity;
use Urbania\Shared\Domain\ValueObjects\Uuid;

interface PropertyDocumentTypeRepositoryInterface
{
    public function findById(Uuid $id): ?PropertyDocumentTypeEntity;

    public function findByCode(string $code): ?PropertyDocumentTypeEntity;

    /**
     * @param  array<string, mixed>  $filters
     * @return array{items: array<PropertyDocumentTypeEntity>, total: int, page: int, perPage: int, lastPage: int}
     */
    public function findAll(array $filters = [], int $page = 1, int $perPage = 20): array;

    public function save(PropertyDocumentTypeEntity $entity): void;

    public function delete(Uuid $id): void;

    public function existsByCode(string $code, ?Uuid $excludeId = null): bool;

    public function hasActiveDocuments(Uuid $id): bool;
}
