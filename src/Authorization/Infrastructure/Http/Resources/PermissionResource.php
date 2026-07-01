<?php

declare(strict_types=1);

namespace Urbania\Authorization\Infrastructure\Http\Resources;

use App\Models\Permission;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

/**
 * @mixin Permission
 */
final class PermissionResource extends JsonResource
{
    /**
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        /** @var Permission $permission */
        $permission = $this->resource;

        return [
            'id' => $permission->id,
            'recurso' => $permission->resource,
            'accion' => $permission->action,
            'nombre' => $permission->name,
            'descripcion' => $permission->description,
        ];
    }
}
