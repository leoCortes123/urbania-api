<?php

declare(strict_types=1);

namespace Urbania\Authorization\Infrastructure\Http\Resources;

use App\Models\PermissionAuditLog;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

/**
 * @mixin PermissionAuditLog
 */
final class AuditLogResource extends JsonResource
{
    /**
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        /** @var PermissionAuditLog $log */
        $log = $this->resource;

        return [
            'id' => $log->id,
            'user' => $log->user === null ? null : [
                'id' => $log->user->id,
                'name' => $log->user->name,
            ],
            'action' => $log->action,
            'resource' => $log->resource,
            'result' => $log->result,
            'context' => $log->context,
            'created_at' => $log->created_at,
        ];
    }
}
