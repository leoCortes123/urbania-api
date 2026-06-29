<?php

declare(strict_types=1);

namespace Urbania\Authorization\Infrastructure;

use Illuminate\Support\ServiceProvider;
use Urbania\Authorization\Domain\Repositories\RoleRepositoryInterface;
use Urbania\Authorization\Domain\Services\PermissionResolverInterface;
use Urbania\Authorization\Infrastructure\Persistence\EloquentRoleRepository;
use Urbania\Authorization\Infrastructure\Services\CachedPermissionResolver;

final class AuthorizationServiceProvider extends ServiceProvider
{
    public function register(): void
    {
        $this->app->bind(RoleRepositoryInterface::class, EloquentRoleRepository::class);
        $this->app->singleton(PermissionResolverInterface::class, CachedPermissionResolver::class);
    }
}
