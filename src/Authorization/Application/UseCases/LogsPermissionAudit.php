<?php

declare(strict_types=1);

namespace Urbania\Authorization\Application\UseCases;

use Illuminate\Support\Facades\DB;

trait LogsPermissionAudit
{
    /**
     * @param  array<string, mixed>  $context
     */
    private function logPermissionAudit(?string $userId, string $action, ?string $resource, string $result, array $context = []): void
    {
        DB::table('permission_audit_log')->insert([
            'id' => DB::raw('gen_random_uuid()'),
            'user_id' => $userId,
            'action' => $action,
            'resource' => $resource,
            'result' => $result,
            'context' => $context === [] ? null : json_encode($context, JSON_THROW_ON_ERROR),
            'created_at' => now(),
        ]);
    }
}
