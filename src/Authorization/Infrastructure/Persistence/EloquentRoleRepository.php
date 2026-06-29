<?php

declare(strict_types=1);

namespace Urbania\Authorization\Infrastructure\Persistence;

use App\Models\Permission as PermissionModel;
use App\Models\Role as RoleModel;
use Urbania\Authorization\Domain\Entities\Permission;
use Urbania\Authorization\Domain\Entities\Role;
use Urbania\Authorization\Domain\Repositories\RoleRepositoryInterface;
use Urbania\Shared\Domain\ValueObjects\Uuid;

final readonly class EloquentRoleRepository implements RoleRepositoryInterface
{
    /** @return array<Role> */
    public function findByUser(Uuid $userId): array
    {
        $roleModels = RoleModel::whereHas('assignments', function ($q) use ($userId) {
            $q->where('user_id', $userId->toString())
                ->whereNull('deleted_at')
                ->where(function ($q): void {
                    $q->whereNull('ends_at')->orWhere('ends_at', '>', now());
                });
        })->get();

        return $roleModels->map(fn ($model): Role => $this->toEntity($model))->all();
    }

    public function findByCode(string $code): ?Role
    {
        $model = RoleModel::where('code', $code)->first();

        return $model !== null ? $this->toEntity($model) : null;
    }

    /** @return array<Permission> */
    public function getRolePermissions(Uuid $roleId): array
    {
        $permissions = PermissionModel::whereHas('roles', function ($q) use ($roleId) {
            $q->where('role_id', $roleId->toString());
        })->get();

        return $permissions->map(
            fn ($model): Permission => new Permission(
                Uuid::fromString($model->id),
                $model->resource,
                $model->action,
                $model->name,
            )
        )->all();
    }

    private function toEntity(RoleModel $model): Role
    {
        return new Role(
            Uuid::fromString($model->id),
            $model->name,
            $model->code,
            $model->level,
            (bool) $model->is_system,
            $model->organization_id !== null ? Uuid::fromString($model->organization_id) : null,
        );
    }
}
