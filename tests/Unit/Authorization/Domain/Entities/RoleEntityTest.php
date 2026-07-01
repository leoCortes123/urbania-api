<?php

declare(strict_types=1);

namespace Tests\Unit\Authorization\Domain\Entities;

use Urbania\Authorization\Domain\Entities\Role;
use Urbania\Shared\Domain\ValueObjects\Uuid;

function createRole(array $overrides = []): Role
{
    return new Role(
        id: $overrides['id'] ?? Uuid::v7(),
        name: $overrides['name'] ?? 'Administrador',
        code: $overrides['code'] ?? 'admin',
        level: $overrides['level'] ?? 'organization',
        isSystem: $overrides['isSystem'] ?? false,
        organizationId: $overrides['organizationId'] ?? null,
    );
}

it('constructor assigns correct values and getters return them', function (): void {
    $id = Uuid::v7();
    $orgId = Uuid::v7();

    $role = createRole([
        'id' => $id,
        'name' => 'Gestor',
        'code' => 'manager',
        'level' => 'condominium',
        'isSystem' => false,
        'organizationId' => $orgId,
    ]);

    expect($role->id()->toString())->toBe($id->toString())
        ->and($role->name())->toBe('Gestor')
        ->and($role->code())->toBe('manager')
        ->and($role->level())->toBe('condominium')
        ->and($role->isSystem())->toBeFalse()
        ->and($role->organizationId()->toString())->toBe($orgId->toString());
});

it('organizationId can be null', function (): void {
    $role = createRole(['organizationId' => null]);

    expect($role->organizationId())->toBeNull();
});

it('isSystem returns true when role is system', function (): void {
    $systemRole = createRole(['isSystem' => true]);
    $regularRole = createRole(['isSystem' => false]);

    expect($systemRole->isSystem())->toBeTrue()
        ->and($regularRole->isSystem())->toBeFalse();
});

it('id returns correct Uuid', function (): void {
    $id = Uuid::v7();
    $role = createRole(['id' => $id]);

    expect($role->id()->toString())->toBe($id->toString())
        ->and($role->id())->toBeInstanceOf(Uuid::class);
});
