<?php

declare(strict_types=1);

namespace Tests\Unit\Authorization\Domain\Entities;

use Urbania\Authorization\Domain\Entities\Permission;
use Urbania\Shared\Domain\ValueObjects\Uuid;

function createPermission(array $overrides = []): Permission
{
    return new Permission(
        id: $overrides['id'] ?? Uuid::v7(),
        resource: $overrides['resource'] ?? 'roles',
        action: $overrides['action'] ?? 'read',
        name: $overrides['name'] ?? 'Ver roles',
    );
}

it('constructor assigns correct values', function (): void {
    $id = Uuid::v7();

    $permission = createPermission([
        'id' => $id,
        'resource' => 'users',
        'action' => 'create',
        'name' => 'Crear usuarios',
    ]);

    expect($permission->id()->toString())->toBe($id->toString())
        ->and($permission->resource())->toBe('users')
        ->and($permission->action())->toBe('create')
        ->and($permission->name())->toBe('Crear usuarios');
});

it('matches returns true when resource and action match', function (): void {
    $permission = createPermission(['resource' => 'properties', 'action' => 'delete']);

    expect($permission->matches('properties', 'delete'))->toBeTrue();
});

it('matches returns false when resource does not match', function (): void {
    $permission = createPermission(['resource' => 'properties', 'action' => 'delete']);

    expect($permission->matches('roles', 'delete'))->toBeFalse();
});

it('matches returns false when action does not match', function (): void {
    $permission = createPermission(['resource' => 'properties', 'action' => 'delete']);

    expect($permission->matches('properties', 'read'))->toBeFalse();
});

it('matches returns false when neither resource nor action match', function (): void {
    $permission = createPermission(['resource' => 'properties', 'action' => 'delete']);

    expect($permission->matches('roles', 'create'))->toBeFalse();
});
