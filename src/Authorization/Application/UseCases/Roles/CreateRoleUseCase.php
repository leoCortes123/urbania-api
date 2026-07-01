<?php

declare(strict_types=1);

namespace Urbania\Authorization\Application\UseCases\Roles;

use App\Models\Role;
use Illuminate\Support\Facades\DB;
use Ramsey\Uuid\Uuid;
use Urbania\Authorization\Application\DTOs\CreateRoleRequestDto;
use Urbania\Authorization\Application\UseCases\InvalidatesPermissionCache;
use Urbania\Authorization\Application\UseCases\LogsPermissionAudit;
use Urbania\Authorization\Domain\Exceptions\RoleNameAlreadyExistsException;
use Urbania\Authorization\Domain\Exceptions\RoleNotFoundException;

final readonly class CreateRoleUseCase
{
    use InvalidatesPermissionCache;
    use LogsPermissionAudit;

    public function execute(CreateRoleRequestDto $dto, string $actorUserId): Role
    {
        if ($this->nameExists($dto->name, null, $dto->organizationId)) {
            throw new RoleNameAlreadyExistsException;
        }

        $role = Role::create([
            'id' => Uuid::uuid7()->toString(),
            'name' => $dto->name,
            'code' => $this->generateCode($dto->name),
            'description' => $dto->description,
            'level' => $dto->level,
            'organization_id' => $dto->organizationId,
            'is_system' => false,
            'is_active' => true,
        ]);

        if ($dto->baseRoleId !== null) {
            $this->copyPermissions($role, $dto->baseRoleId, $dto->organizationId);
        }

        $this->logPermissionAudit($actorUserId, 'create_role', 'roles', 'granted', [
            'role_id' => $role->id,
            'role_name' => $role->name,
        ]);

        return $role;
    }

    private function nameExists(string $name, ?string $excludeId, string $organizationId): bool
    {
        $query = Role::query()
            ->where('name', $name)
            ->where(static function ($query) use ($organizationId): void {
                $query->where('organization_id', $organizationId)
                    ->orWhere('is_system', true);
            });

        if ($excludeId !== null) {
            $query->where('id', '!=', $excludeId);
        }

        return $query->exists();
    }

    private function generateCode(string $name): string
    {
        $base = strtolower(trim(preg_replace('/[^a-zA-Z0-9]+/', '_', $name) ?: $name, '_'));

        return $base.'_'.substr(Uuid::uuid7()->toString(), 0, 8);
    }

    private function copyPermissions(Role $role, string $baseRoleId, string $organizationId): void
    {
        $baseRole = Role::where('id', $baseRoleId)
            ->where(static function ($query) use ($organizationId): void {
                $query->where('organization_id', $organizationId)
                    ->orWhere('is_system', true);
            })
            ->first();

        if ($baseRole === null) {
            throw new RoleNotFoundException('El rol base no existe');
        }

        $permissionIds = DB::table('role_permissions')
            ->where('role_id', $baseRole->id)
            ->pluck('permission_id');

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
    }
}
