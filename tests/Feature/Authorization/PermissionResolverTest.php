<?php

declare(strict_types=1);

use App\Models\Organization;
use App\Models\Role;
use App\Models\RoleAssignment;
use App\Models\User;
use Database\Seeders\RbacPermissionSeeder;
use Database\Seeders\RbacRoleSeeder;
use Illuminate\Foundation\Testing\LazilyRefreshDatabase;
use Illuminate\Support\Facades\Cache;
use Urbania\Authorization\Domain\Services\PermissionResolverInterface;
use Urbania\Shared\Domain\ValueObjects\Uuid;

uses(LazilyRefreshDatabase::class);

beforeEach(function (): void {
    Cache::flush();
    $this->seed([
        RbacPermissionSeeder::class,
        RbacRoleSeeder::class,
    ]);
});

it('resolves organization permissions for an admin user', function (): void {
    $organization = Organization::factory()->create();
    $user = User::factory()->create(['organization_id' => $organization->id]);

    $adminRole = Role::where('code', 'admin')->firstOrFail();
    RoleAssignment::create([
        'user_id' => $user->id,
        'role_id' => $adminRole->id,
        'scope_type' => 'organization',
        'scope_id' => $organization->id,
    ]);

    $resolver = app(PermissionResolverInterface::class);
    $permissions = $resolver->resolvePermissions(
        Uuid::fromString($user->id),
        'organization',
        Uuid::fromString($organization->id)
    );

    expect($permissions)->toContain('propiedades.ver')
        ->and($permissions)->toContain('propiedades.crear')
        ->and($permissions)->toContain('directorio.ver');
});

it('caches resolved permissions', function (): void {
    $organization = Organization::factory()->create();
    $user = User::factory()->create(['organization_id' => $organization->id]);

    $adminRole = Role::where('code', 'admin')->firstOrFail();
    RoleAssignment::create([
        'user_id' => $user->id,
        'role_id' => $adminRole->id,
        'scope_type' => 'organization',
        'scope_id' => $organization->id,
    ]);

    $resolver = app(PermissionResolverInterface::class);
    $userUuid = Uuid::fromString($user->id);
    $orgUuid = Uuid::fromString($organization->id);

    $first = $resolver->resolvePermissions($userUuid, 'organization', $orgUuid);
    $second = $resolver->resolvePermissions($userUuid, 'organization', $orgUuid);

    expect($first)->toEqual($second)
        ->and(Cache::has("perms:{$user->id}:organization:{$organization->id}"))->toBeTrue();
});

it('denies permission for user without role assignment', function (): void {
    $organization = Organization::factory()->create();
    $user = User::factory()->create(['organization_id' => $organization->id]);

    $resolver = app(PermissionResolverInterface::class);

    $can = $resolver->can(
        Uuid::fromString($user->id),
        'propiedades',
        'crear',
        'organization',
        Uuid::fromString($organization->id)
    );

    expect($can)->toBeFalse();
});

it('resolves permissions for a resident user', function (): void {
    $organization = Organization::factory()->create();
    $user = User::factory()->create(['organization_id' => $organization->id]);

    $residentRole = Role::where('code', 'residente')->firstOrFail();
    RoleAssignment::create([
        'user_id' => $user->id,
        'role_id' => $residentRole->id,
        'scope_type' => 'organization',
        'scope_id' => $organization->id,
    ]);

    $resolver = app(PermissionResolverInterface::class);
    $permissions = $resolver->resolvePermissions(
        Uuid::fromString($user->id),
        'organization',
        Uuid::fromString($organization->id)
    );

    expect($permissions)->toContain('comunicaciones.ver')
        ->and($permissions)->toContain('reservas.crear');
});

it('denies permission that the role does not have', function (): void {
    $organization = Organization::factory()->create();
    $user = User::factory()->create(['organization_id' => $organization->id]);

    $adminRole = Role::where('code', 'admin')->firstOrFail();
    RoleAssignment::create([
        'user_id' => $user->id,
        'role_id' => $adminRole->id,
        'scope_type' => 'organization',
        'scope_id' => $organization->id,
    ]);

    $resolver = app(PermissionResolverInterface::class);

    $can = $resolver->can(
        Uuid::fromString($user->id),
        'cobranza',
        'generar',
        'organization',
        Uuid::fromString($organization->id)
    );

    expect($can)->toBeFalse();
});
