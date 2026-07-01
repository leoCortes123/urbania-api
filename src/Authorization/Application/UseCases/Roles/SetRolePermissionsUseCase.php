<?php

declare(strict_types=1);

namespace Urbania\Authorization\Application\UseCases\Roles;

use App\Models\Permission;
use App\Models\Role;
use App\Models\RoleAssignment;
use Illuminate\Support\Facades\DB;
use Ramsey\Uuid\Uuid;
use Urbania\Authorization\Application\DTOs\SetRolePermissionsRequestDto;
use Urbania\Authorization\Application\UseCases\InvalidatesPermissionCache;
use Urbania\Authorization\Application\UseCases\LogsPermissionAudit;
use Urbania\Authorization\Domain\Exceptions\RoleNotFoundException;

final readonly class SetRolePermissionsUseCase
{
    use InvalidatesPermissionCache;
    use LogsPermissionAudit;

    public function execute(SetRolePermissionsRequestDto $dto, string $actorUserId): Role
    {
        $role = Role::find($dto->roleId);

        if ($role === null) {
            throw new RoleNotFoundException;
        }

        if (! $role->is_system && $role->organization_id !== $dto->organizationId) {
            throw new RoleNotFoundException;
        }

        $permissionIds = $this->resolvePermissionIds($dto->permissions);

        DB::table('role_permissions')->where('role_id', $role->id)->delete();

        $now = now();
        $rows = [];

        foreach ($permissionIds as $permissionId) {
            $rows[] = [
                'id' => Uuid::uuid7()->toString(),
                'role_id' => $role->id,
                'permission_id' => $permissionId,
                'created_at' => $now,
                'updated_at' => $now,
            ];
        }

        if ($rows !== []) {
            DB::table('role_permissions')->insert($rows);
        }

        $this->invalidateCacheForRole($role->id);

        $this->logPermissionAudit($actorUserId, 'set_permissions', 'roles', 'granted', [
            'role_id' => $role->id,
            'permissions' => $dto->permissions,
        ]);

        return $role;
    }

    /**
     * @param  array<int, string>  $permissions
     * @return array<int, string>
     */
    private function resolvePermissionIds(array $permissions): array
    {
        if ($permissions === []) {
            return [];
        }

        $resolved = [];

        foreach ($permissions as $key) {
            $parts = explode('.', $key);

            if (count($parts) !== 2) {
                continue;
            }

            [$resource, $action] = $parts;
            $permission = Permission::where('resource', $resource)
                ->where('action', $action)
                ->first();

            if ($permission !== null) {
                $resolved[] = $permission->id;
            }
        }

        return $resolved;
    }

    private function invalidateCacheForRole(string $roleId): void
    {
        /** @var array<int, string> $userIds */
        $userIds = RoleAssignment::where('role_id', $roleId)
            ->whereNull('deleted_at')
            ->pluck('user_id')
            ->unique()
            ->all();

        foreach ($userIds as $userId) {
            $this->invalidatePermissionCacheForUser($userId);
        }
    }
}
