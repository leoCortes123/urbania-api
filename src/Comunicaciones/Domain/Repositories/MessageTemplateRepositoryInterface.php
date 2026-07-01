<?php

declare(strict_types=1);

namespace Urbania\Comunicaciones\Domain\Repositories;

use Urbania\Comunicaciones\Domain\Entities\MessageTemplateEntity;
use Urbania\Shared\Domain\ValueObjects\Uuid;

interface MessageTemplateRepositoryInterface
{
    public function findById(Uuid $id): ?MessageTemplateEntity;

    /**
     * @param  array<string, mixed>  $filters
     * @return array{items: array<MessageTemplateEntity>, total: int, page: int, perPage: int, lastPage: int}
     */
    public function findByCondominiumId(Uuid $condominiumId, array $filters = [], int $page = 1, int $perPage = 20): array;

    public function save(MessageTemplateEntity $entity): void;

    public function delete(Uuid $id): void;
}
