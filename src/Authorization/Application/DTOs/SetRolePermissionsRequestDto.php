<?php

declare(strict_types=1);

namespace Urbania\Authorization\Application\DTOs;

final readonly class SetRolePermissionsRequestDto
{
    /**
     * @param  array<int, string>  $permissions
     */
    public function __construct(
        public string $roleId,
        public array $permissions,
        public string $organizationId,
    ) {}
}
