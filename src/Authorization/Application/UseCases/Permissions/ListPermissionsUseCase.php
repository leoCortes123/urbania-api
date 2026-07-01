<?php

declare(strict_types=1);

namespace Urbania\Authorization\Application\UseCases\Permissions;

use App\Models\Permission;
use Illuminate\Support\Collection;

final readonly class ListPermissionsUseCase
{
    /**
     * @return Collection<string, mixed>
     */
    public function execute(): Collection
    {
        /** @var Collection<string, mixed> $groups */
        $groups = Permission::orderBy('resource')
            ->orderBy('action')
            ->get()
            ->groupBy('resource')
            ->toBase();

        return $groups;
    }
}
