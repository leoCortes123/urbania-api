<?php

declare(strict_types=1);

namespace Urbania\Authorization\Domain\Repositories;

use Urbania\Authorization\Domain\Entities\Permission;
use Urbania\Authorization\Domain\Entities\Role;
use Urbania\Shared\Domain\ValueObjects\Uuid;

interface RoleRepositoryInterface
{
    /** @return array<Role> */
    public function findByUser(Uuid $userId): array;

    public function findByCode(string $code): ?Role;

    /** @return array<Permission> */
    public function getRolePermissions(Uuid $roleId): array;
}
