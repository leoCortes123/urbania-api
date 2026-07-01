<?php

declare(strict_types=1);

namespace Urbania\Authorization\Infrastructure\Http\Resources;

use App\Models\Permission;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;
use Illuminate\Support\Collection;

/**
 * @mixin Collection<string, mixed>
 */
final class PermissionGroupCollection extends JsonResource
{
    /**
     * @param  Collection<string, mixed>  $resource
     */
    public function __construct(Collection $resource)
    {
        parent::__construct($resource);
    }

    /**
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        /** @var Collection<string, Collection<int, Permission>> $groups */
        $groups = $this->resource;

        return [
            'data' => $groups->map(
                static fn (Collection $permissions, string $resource): array => [
                    'recurso' => $resource,
                    'permisos' => $permissions->map(
                        static fn (Permission $permission): array => (new PermissionResource($permission))->resolve($request)
                    )->all(),
                ]
            )->values()->all(),
        ];
    }
}
