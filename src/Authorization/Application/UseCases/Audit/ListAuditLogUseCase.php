<?php

declare(strict_types=1);

namespace Urbania\Authorization\Application\UseCases\Audit;

use App\Models\PermissionAuditLog;
use App\Models\User;
use Illuminate\Pagination\LengthAwarePaginator;

final readonly class ListAuditLogUseCase
{
    /**
     * @param  array<string, mixed>  $filters
     * @return LengthAwarePaginator<int, PermissionAuditLog>
     */
    public function execute(string $organizationId, array $filters, int $page, int $perPage): LengthAwarePaginator
    {
        $query = PermissionAuditLog::with('user');

        $actor = $filters['actor'] ?? null;
        if (is_string($actor) && $actor !== '') {
            $actorBelongs = User::where('id', $actor)
                ->where('organization_id', $organizationId)
                ->exists();

            if (! $actorBelongs) {
                return new LengthAwarePaginator(
                    [],
                    0,
                    $perPage,
                    $page,
                    ['path' => request()->url()]
                );
            }

            $query->where('user_id', $actor);
        } else {
            $query->whereIn(
                'user_id',
                User::query()->select('id')->where('organization_id', $organizationId)
            );
        }

        $from = $filters['from'] ?? null;
        if (is_string($from) && $from !== '') {
            $query->whereDate('created_at', '>=', $from);
        }

        $to = $filters['to'] ?? null;
        if (is_string($to) && $to !== '') {
            $query->whereDate('created_at', '<=', $to);
        }

        return $query->orderByDesc('created_at')
            ->paginate($perPage, ['*'], 'page', $page);
    }
}
