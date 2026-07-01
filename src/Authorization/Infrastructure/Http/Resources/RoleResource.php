<?php

declare(strict_types=1);

namespace Urbania\Authorization\Infrastructure\Http\Resources;

use App\Models\Role;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

/**
 * @mixin Role
 */
final class RoleResource extends JsonResource
{
    /**
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        /** @var Role $role */
        $role = $this->resource;

        return [
            'id' => $role->id,
            'nombre' => $role->name,
            'codigo' => $role->code,
            'descripcion' => $role->description,
            'es_sistema' => $role->is_system,
            'nivel_alcance' => $role->level,
            'usuarios_count' => $role->assignments_count ?? 0,
            'created_at' => $role->created_at,
            'updated_at' => $role->updated_at,
        ];
    }
}
