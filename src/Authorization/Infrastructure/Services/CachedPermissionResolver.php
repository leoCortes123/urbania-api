<?php

declare(strict_types=1);

namespace Urbania\Authorization\Infrastructure\Services;

use App\Models\PropertyOccupant;
use Illuminate\Support\Facades\Cache;
use Urbania\Authorization\Domain\Repositories\RoleRepositoryInterface;
use Urbania\Authorization\Domain\Services\PermissionResolverInterface;
use Urbania\Shared\Domain\ValueObjects\Uuid;

final readonly class CachedPermissionResolver implements PermissionResolverInterface
{
    private const string CACHE_PREFIX = 'perms:';

    private const int CACHE_TTL = 300; // 5 minutos

    public function __construct(
        private RoleRepositoryInterface $roleRepository,
    ) {}

    public function resolvePermissions(Uuid $userId, string $scopeType, Uuid $scopeId): array
    {
        $cacheKey = self::CACHE_PREFIX.$userId->toString().":{$scopeType}:{$scopeId->toString()}";

        /** @var array<string> $resolved */
        $resolved = Cache::remember($cacheKey, self::CACHE_TTL, function () use ($userId, $scopeType, $scopeId): array {
            $permissions = [];
            $roles = $this->roleRepository->findByUser($userId);

            foreach ($roles as $role) {
                $rolePerms = $this->roleRepository->getRolePermissions($role->id());
                foreach ($rolePerms as $perm) {
                    $key = "{$perm->resource()}.{$perm->action()}";
                    $permissions[$key] = true;
                }
            }

            if ($scopeType === 'unit') {
                $this->addResidentPermissions($userId, $scopeId, $permissions);
            }

            return array_keys($permissions);
        });

        return $resolved;
    }

    public function can(Uuid $userId, string $resource, string $action, string $scopeType, Uuid $scopeId): bool
    {
        $permissions = $this->resolvePermissions($userId, $scopeType, $scopeId);

        return in_array("{$resource}.{$action}", $permissions, true);
    }

    /**
     * Agrega permisos de residente derivados de property_occupants.
     *
     * @param  array<string, true>  $permissions
     */
    private function addResidentPermissions(Uuid $userId, Uuid $scopeId, array &$permissions): void
    {
        $isResident = PropertyOccupant::query()
            ->whereHas('contact', function ($q) use ($userId): void {
                $q->where('user_id', $userId->toString());
            })
            ->where('property_id', $scopeId->toString())
            ->where('is_active', true)
            ->whereNull('deleted_at')
            ->exists();

        if (! $isResident) {
            return;
        }

        $residentRole = $this->roleRepository->findByCode('residente');
        if ($residentRole === null) {
            return;
        }

        $rolePerms = $this->roleRepository->getRolePermissions($residentRole->id());
        foreach ($rolePerms as $perm) {
            $key = "{$perm->resource()}.{$perm->action()}";
            $permissions[$key] = true;
        }
    }
}
