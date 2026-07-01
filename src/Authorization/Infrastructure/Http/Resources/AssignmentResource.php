<?php

declare(strict_types=1);

namespace Urbania\Authorization\Infrastructure\Http\Resources;

use App\Models\RoleAssignment;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

/**
 * @mixin RoleAssignment
 */
final class AssignmentResource extends JsonResource
{
    /**
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        /** @var RoleAssignment $assignment */
        $assignment = $this->resource;

        return [
            'id' => $assignment->id,
            'user_id' => $assignment->user_id,
            'role_id' => $assignment->role_id,
            'role_name' => $assignment->role?->name,
            'scope_type' => $assignment->scope_type,
            'scope_id' => $assignment->scope_id,
            'vigencia_inicio' => $assignment->starts_at,
            'vigencia_fin' => $assignment->ends_at,
            'created_at' => $assignment->created_at,
        ];
    }
}
