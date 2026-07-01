<?php

declare(strict_types=1);

namespace Urbania\Authorization\Application\UseCases\Roles;

use App\Models\Role;
use Urbania\Authorization\Application\DTOs\UpdateRoleRequestDto;
use Urbania\Authorization\Application\UseCases\LogsPermissionAudit;
use Urbania\Authorization\Domain\Exceptions\RoleIsSystemException;
use Urbania\Authorization\Domain\Exceptions\RoleNameAlreadyExistsException;
use Urbania\Authorization\Domain\Exceptions\RoleNotFoundException;

final readonly class UpdateRoleUseCase
{
    use LogsPermissionAudit;

    public function execute(
        string $roleId,
        UpdateRoleRequestDto $dto,
        string $organizationId,
        bool $allowSystemEdit,
        string $actorUserId,
    ): Role {
        $role = Role::find($roleId);

        if ($role === null) {
            throw new RoleNotFoundException;
        }

        if ($role->is_system && ! $allowSystemEdit) {
            throw new RoleIsSystemException;
        }

        if (! $role->is_system && $role->organization_id !== $organizationId) {
            throw new RoleNotFoundException;
        }

        if ($dto->name !== null) {
            if ($this->nameExists($dto->name, $roleId, $organizationId)) {
                throw new RoleNameAlreadyExistsException;
            }

            $role->name = $dto->name;
        }

        if ($dto->description !== null) {
            $role->description = $dto->description;
        }

        if ($dto->level !== null) {
            $role->level = $dto->level;
        }

        $role->save();

        $this->logPermissionAudit($actorUserId, 'update_role', 'roles', 'granted', [
            'role_id' => $role->id,
            'role_name' => $role->name,
            'changes' => $dto,
        ]);

        return $role;
    }

    private function nameExists(string $name, string $excludeId, string $organizationId): bool
    {
        return Role::query()
            ->where('id', '!=', $excludeId)
            ->where('name', $name)
            ->where(static function ($query) use ($organizationId): void {
                $query->where('organization_id', $organizationId)
                    ->orWhere('is_system', true);
            })
            ->exists();
    }
}
