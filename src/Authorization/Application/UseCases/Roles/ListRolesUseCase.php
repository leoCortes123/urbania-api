<?php

declare(strict_types=1);

namespace Urbania\Authorization\Application\UseCases\Roles;

use App\Models\Role;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Collection;

final readonly class ListRolesUseCase
{
    /**
     * @return Collection<int, Role>
     */
    public function execute(string $organizationId): Collection
    {
        return Role::withCount([
            'assignments' => static function (Builder $query): void {
                $query->whereNull('deleted_at');
            },
        ])
            ->where(static function (Builder $query) use ($organizationId): void {
                $query->where('is_system', true)
                    ->orWhere('organization_id', $organizationId);
            })
            ->orderBy('is_system', 'desc')
            ->orderBy('name')
            ->get();
    }
}
