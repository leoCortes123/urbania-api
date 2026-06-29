<?php

declare(strict_types=1);

namespace Urbania\Tenancy\Domain\Repositories;

use Urbania\Shared\Domain\ValueObjects\Uuid;

interface OrganizationRepositoryInterface
{
    public function findById(Uuid $id): ?object;

    public function exists(string $id): bool;
}
