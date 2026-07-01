<?php

declare(strict_types=1);

namespace Urbania\Authorization\Infrastructure\Http\Resources;

use App\Models\Role;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

/**
 * @mixin Collection<int, Role>
 */
final class RoleCollection extends JsonResource
{
    /**
     * @param  Collection<int, Role>  $resource
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
        /** @var Collection<int, Role> $collection */
        $collection = $this->resource;

        return [
            'data' => $collection->map(
                static fn (Role $role): array => (new RoleResource($role))->resolve($request)
            )->all(),
        ];
    }
}
