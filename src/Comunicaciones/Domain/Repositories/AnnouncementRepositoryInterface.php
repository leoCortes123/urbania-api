<?php

declare(strict_types=1);

namespace Urbania\Comunicaciones\Domain\Repositories;

use Urbania\Comunicaciones\Domain\Entities\AnnouncementEntity;
use Urbania\Shared\Domain\ValueObjects\Uuid;

interface AnnouncementRepositoryInterface
{
    public function findById(Uuid $id): ?AnnouncementEntity;

    /**
     * @param  array<string, mixed>  $filters
     * @return array{items: array<AnnouncementEntity>, total: int, page: int, perPage: int, lastPage: int}
     */
    public function findByCondominiumId(Uuid $condominiumId, array $filters = [], int $page = 1, int $perPage = 20): array;

    public function save(AnnouncementEntity $entity): void;

    public function delete(Uuid $id): void;
}
