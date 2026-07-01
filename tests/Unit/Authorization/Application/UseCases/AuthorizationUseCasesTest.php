<?php

declare(strict_types=1);

namespace Tests\Unit\Authorization\Application\UseCases;

use App\Models\Organization;
use App\Models\Permission;
use App\Models\Role;
use App\Models\RoleAssignment;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Pagination\LengthAwarePaginator;
use Illuminate\Support\Collection as SupportCollection;
use Illuminate\Support\Facades\DB;
use Tests\TestCase;
use Urbania\Authorization\Application\DTOs\CreateApprovalRuleRequestDto;
use Urbania\Authorization\Application\DTOs\CreateAssignmentRequestDto;
use Urbania\Authorization\Application\DTOs\CreateRoleRequestDto;
use Urbania\Authorization\Application\DTOs\SetRolePermissionsRequestDto;
use Urbania\Authorization\Application\DTOs\UpdateRoleRequestDto;
use Urbania\Authorization\Application\UseCases\ApprovalRules\CreateApprovalRuleUseCase;
use Urbania\Authorization\Application\UseCases\Assignments\CreateAssignmentUseCase;
use Urbania\Authorization\Application\UseCases\Assignments\RevokeAssignmentUseCase;
use Urbania\Authorization\Application\UseCases\Audit\ListAuditLogUseCase;
use Urbania\Authorization\Application\UseCases\Permissions\ListPermissionsUseCase;
use Urbania\Authorization\Application\UseCases\Roles\CreateRoleUseCase;
use Urbania\Authorization\Application\UseCases\Roles\ListRolesUseCase;
use Urbania\Authorization\Application\UseCases\Roles\SetRolePermissionsUseCase;
use Urbania\Authorization\Application\UseCases\Roles\UpdateRoleUseCase;
use Urbania\Authorization\Domain\Exceptions\ApprovalRuleInvalidApproverException;
use Urbania\Authorization\Domain\Exceptions\AssignmentAlreadyExistsException;
use Urbania\Authorization\Domain\Exceptions\AssignmentNotFoundException;
use Urbania\Authorization\Domain\Exceptions\RoleIsSystemException;
use Urbania\Authorization\Domain\Exceptions\RoleNameAlreadyExistsException;
use Urbania\Authorization\Domain\Exceptions\RoleNotFoundException;

uses(TestCase::class);
uses(RefreshDatabase::class);

// UUIDs válidos
define('ORG_ID', '00000000-0000-0000-0000-000000000001');
define('OTHER_ORG_ID', '00000000-0000-0000-0000-000000000002');
define('ACTOR_ID', '00000000-0000-0000-0000-000000000010');
define('USER1_ID', '00000000-0000-0000-0000-000000000011');
define('USER2_ID', '00000000-0000-0000-0000-000000000012');
define('USER3_ID', '00000000-0000-0000-0000-000000000013');
define('SCOPE_ID', '00000000-0000-0000-0000-000000000020');
define('SCOPE2_ID', '00000000-0000-0000-0000-000000000021');
define('SCOPE3_ID', '00000000-0000-0000-0000-000000000022');
define('ADMIN_ID', '00000000-0000-0000-0000-000000000030');

beforeEach(function (): void {
    // Crear organizaciones usando factory para respetar defaults de BD
    Organization::factory()->create(['id' => ORG_ID, 'name' => 'Test Org']);
    Organization::factory()->create(['id' => OTHER_ORG_ID, 'name' => 'Other Org']);

    // Crear permisos de prueba
    Permission::insert([
        ['id' => 'aaaaaaaa-aaaa-aaaa-aaaa-aaaaaaaaaa01', 'resource' => 'properties', 'action' => 'read', 'name' => 'Ver'],
        ['id' => 'aaaaaaaa-aaaa-aaaa-aaaa-aaaaaaaaaa02', 'resource' => 'properties', 'action' => 'write', 'name' => 'Editar'],
        ['id' => 'aaaaaaaa-aaaa-aaaa-aaaa-aaaaaaaaaa03', 'resource' => 'roles', 'action' => 'read', 'name' => 'Ver roles'],
    ]);

    // Crear un rol de sistema
    Role::insert([
        'id' => 'bbbbbbbb-bbbb-bbbb-bbbb-bbbbbbbbbb01',
        'name' => 'System Admin',
        'code' => 'system_admin',
        'description' => 'System role',
        'level' => 'system',
        'organization_id' => null,
        'is_system' => true,
        'is_active' => true,
    ]);

    // Crear usuarios para FK constraints en role_assignments
    User::insert([
        ['id' => USER1_ID, 'email' => 'user1@test.com', 'name' => 'User 1', 'password_hash' => 'x', 'organization_id' => ORG_ID],
        ['id' => USER2_ID, 'email' => 'user2@test.com', 'name' => 'User 2', 'password_hash' => 'x', 'organization_id' => ORG_ID],
        ['id' => USER3_ID, 'email' => 'user3@test.com', 'name' => 'User 3', 'password_hash' => 'x', 'organization_id' => ORG_ID],
        ['id' => ACTOR_ID, 'email' => 'actor@test.com', 'name' => 'Actor', 'password_hash' => 'x', 'organization_id' => ORG_ID],
        ['id' => ADMIN_ID, 'email' => 'admin@test.com', 'name' => 'Admin', 'password_hash' => 'x', 'organization_id' => ORG_ID],
    ]);
});

// ═══════════════════════════════════════════════════════════════════════════════
// CreateRoleUseCase
// ═══════════════════════════════════════════════════════════════════════════════

describe('CreateRoleUseCase', function (): void {
    it('creates a role correctly', function (): void {
        $dto = new CreateRoleRequestDto('Manager', 'A manager role', 'org', null, ORG_ID);
        $result = (new CreateRoleUseCase)->execute($dto, ACTOR_ID);

        expect($result)->toBeInstanceOf(Role::class)
            ->and($result->name)->toBe('Manager')
            ->and($result->organization_id)->toBe(ORG_ID)
            ->and($result->is_system)->toBeFalse()
            ->and($result->is_active)->toBeTrue();
    });

    it('throws RoleNameAlreadyExistsException when name already exists in same org', function (): void {
        Role::create([
            'id' => 'cccccccc-cccc-cccc-cccc-cccccccccc01',
            'name' => 'Manager',
            'code' => 'manager_01',
            'level' => 'org',
            'organization_id' => ORG_ID,
            'is_system' => false,
            'is_active' => true,
        ]);

        $dto = new CreateRoleRequestDto('Manager', null, 'org', null, ORG_ID);
        expect(fn () => (new CreateRoleUseCase)->execute($dto, ACTOR_ID))
            ->toThrow(RoleNameAlreadyExistsException::class);
    });

    it('throws RoleNameAlreadyExistsException when name matches system role', function (): void {
        $dto = new CreateRoleRequestDto('System Admin', null, 'org', null, ORG_ID);
        expect(fn () => (new CreateRoleUseCase)->execute($dto, ACTOR_ID))
            ->toThrow(RoleNameAlreadyExistsException::class);
    });

    it('copies permissions from baseRoleId', function (): void {
        $baseRole = Role::create([
            'id' => 'dddddddd-dddd-dddd-dddd-dddddddddd01',
            'name' => 'Base Role',
            'code' => 'base_role_01',
            'level' => 'org',
            'organization_id' => ORG_ID,
            'is_system' => false,
            'is_active' => true,
        ]);

        // Asignar permisos al baseRole
        DB::table('role_permissions')->insert([
            ['id' => 'eeeeeeee-eeee-eeee-eeee-eeeeeeeeee01', 'role_id' => $baseRole->id, 'permission_id' => 'aaaaaaaa-aaaa-aaaa-aaaa-aaaaaaaaaa01'],
            ['id' => 'eeeeeeee-eeee-eeee-eeee-eeeeeeeeee02', 'role_id' => $baseRole->id, 'permission_id' => 'aaaaaaaa-aaaa-aaaa-aaaa-aaaaaaaaaa02'],
        ]);

        $dto = new CreateRoleRequestDto('Copied Role', null, 'org', $baseRole->id, ORG_ID);
        $result = (new CreateRoleUseCase)->execute($dto, ACTOR_ID);

        expect($result->name)->toBe('Copied Role');

        // Verificar que se copiaron los permisos
        $permIds = DB::table('role_permissions')
            ->where('role_id', $result->id)->pluck('permission_id')->all();
        expect($permIds)->toHaveCount(2);
    });

    it('throws RoleNotFoundException for invalid baseRoleId', function (): void {
        $dto = new CreateRoleRequestDto('New', null, 'org', 'ffffffff-ffff-ffff-ffff-ffffffffffff', ORG_ID);
        expect(fn () => (new CreateRoleUseCase)->execute($dto, ACTOR_ID))
            ->toThrow(RoleNotFoundException::class);
    });
});

// ═══════════════════════════════════════════════════════════════════════════════
// UpdateRoleUseCase
// ═══════════════════════════════════════════════════════════════════════════════

describe('UpdateRoleUseCase', function (): void {
    it('updates a role correctly', function (): void {
        $role = Role::create([
            'id' => '11111111-1111-1111-1111-111111111111',
            'name' => 'Old Name',
            'code' => 'old_name_01',
            'description' => 'Old',
            'level' => 'org',
            'organization_id' => ORG_ID,
            'is_system' => false,
            'is_active' => true,
        ]);

        $dto = new UpdateRoleRequestDto('Updated Name', 'New desc', 'admin');
        $result = (new UpdateRoleUseCase)->execute($role->id, $dto, ORG_ID, false, ACTOR_ID);

        expect($result->name)->toBe('Updated Name')
            ->and($result->description)->toBe('New desc')
            ->and($result->level)->toBe('admin');
    });

    it('throws RoleNotFoundException when role does not exist', function (): void {
        $dto = new UpdateRoleRequestDto(null, null, null);
        expect(fn () => (new UpdateRoleUseCase)->execute('00000000-0000-0000-0000-000000000099', $dto, ORG_ID, false, ACTOR_ID))
            ->toThrow(RoleNotFoundException::class);
    });

    it('throws RoleIsSystemException for system role without permission', function (): void {
        $dto = new UpdateRoleRequestDto('Changed', null, null);
        expect(fn () => (new UpdateRoleUseCase)->execute('bbbbbbbb-bbbb-bbbb-bbbb-bbbbbbbbbb01', $dto, ORG_ID, false, ACTOR_ID))
            ->toThrow(RoleIsSystemException::class);
    });

    it('throws RoleNotFoundException for role of another organization', function (): void {
        $role = Role::create([
            'id' => '22222222-2222-2222-2222-222222222222',
            'name' => 'Other Org Role',
            'code' => 'other_org_01',
            'level' => 'org',
            'organization_id' => OTHER_ORG_ID,
            'is_system' => false,
            'is_active' => true,
        ]);

        $dto = new UpdateRoleRequestDto(null, null, null);
        expect(fn () => (new UpdateRoleUseCase)->execute($role->id, $dto, ORG_ID, false, ACTOR_ID))
            ->toThrow(RoleNotFoundException::class);
    });

    it('throws RoleNameAlreadyExistsException when name conflicts', function (): void {
        Role::create([
            'id' => '33333333-3333-3333-3333-333333333333',
            'name' => 'Existing Name',
            'code' => 'existing_01',
            'level' => 'org',
            'organization_id' => ORG_ID,
            'is_system' => false,
            'is_active' => true,
        ]);

        $role = Role::create([
            'id' => '44444444-4444-4444-4444-444444444444',
            'name' => 'ToUpdate',
            'code' => 'toupdate_01',
            'level' => 'org',
            'organization_id' => ORG_ID,
            'is_system' => false,
            'is_active' => true,
        ]);

        $dto = new UpdateRoleRequestDto('Existing Name', null, null);
        expect(fn () => (new UpdateRoleUseCase)->execute($role->id, $dto, ORG_ID, false, ACTOR_ID))
            ->toThrow(RoleNameAlreadyExistsException::class);
    });
});

// ═══════════════════════════════════════════════════════════════════════════════
// ListRolesUseCase
// ═══════════════════════════════════════════════════════════════════════════════

describe('ListRolesUseCase', function (): void {
    it('lists roles scoped to the organization including system roles', function (): void {
        Role::create([
            'id' => '55555555-5555-5555-5555-555555555555',
            'name' => 'Org Role',
            'code' => 'org_role_01',
            'level' => 'org',
            'organization_id' => ORG_ID,
            'is_system' => false,
            'is_active' => true,
        ]);

        $result = (new ListRolesUseCase)->execute(ORG_ID);

        // Debe incluir el rol del sistema (System Admin) + Org Role
        expect($result)->toHaveCount(2)
            ->and($result->pluck('name')->all())->toContain('System Admin', 'Org Role');
    });

    it('excludes roles from other organizations', function (): void {
        Role::create([
            'id' => '66666666-6666-6666-6666-666666666666',
            'name' => 'Other Org Role',
            'code' => 'other_org_02',
            'level' => 'org',
            'organization_id' => OTHER_ORG_ID,
            'is_system' => false,
            'is_active' => true,
        ]);

        $result = (new ListRolesUseCase)->execute(ORG_ID);

        expect($result->pluck('name')->all())->not()->toContain('Other Org Role');
    });
});

// ═══════════════════════════════════════════════════════════════════════════════
// SetRolePermissionsUseCase
// ═══════════════════════════════════════════════════════════════════════════════

describe('SetRolePermissionsUseCase', function (): void {
    it('assigns permissions to a role', function (): void {
        $role = Role::create([
            'id' => '77777777-7777-7777-7777-777777777777',
            'name' => 'Custom Role',
            'code' => 'custom_01',
            'level' => 'org',
            'organization_id' => ORG_ID,
            'is_system' => false,
            'is_active' => true,
        ]);

        $dto = new SetRolePermissionsRequestDto($role->id, ['properties.read', 'properties.write'], ORG_ID);
        $result = (new SetRolePermissionsUseCase)->execute($dto, ACTOR_ID);

        expect($result->id)->toBe($role->id);

        $permIds = DB::table('role_permissions')
            ->where('role_id', $role->id)->pluck('permission_id')->all();
        expect($permIds)->toHaveCount(2);
    });

    it('replaces existing permissions', function (): void {
        $role = Role::create([
            'id' => '88888888-8888-8888-8888-888888888888',
            'name' => 'Replace Role',
            'code' => 'replace_01',
            'level' => 'org',
            'organization_id' => ORG_ID,
            'is_system' => false,
            'is_active' => true,
        ]);

        // Primero asignar un permiso
        $dto1 = new SetRolePermissionsRequestDto($role->id, ['properties.read'], ORG_ID);
        (new SetRolePermissionsUseCase)->execute($dto1, ACTOR_ID);

        // Luego reemplazar con otro set
        $dto2 = new SetRolePermissionsRequestDto($role->id, ['properties.write'], ORG_ID);
        (new SetRolePermissionsUseCase)->execute($dto2, ACTOR_ID);

        $permIds = DB::table('role_permissions')
            ->where('role_id', $role->id)->pluck('permission_id')->all();
        expect($permIds)->toHaveCount(1);
    });

    it('throws RoleNotFoundException when role does not exist', function (): void {
        $dto = new SetRolePermissionsRequestDto('00000000-0000-0000-0000-000000000099', ['properties.read'], ORG_ID);
        expect(fn () => (new SetRolePermissionsUseCase)->execute($dto, ACTOR_ID))
            ->toThrow(RoleNotFoundException::class);
    });
});

// ═══════════════════════════════════════════════════════════════════════════════
// ListPermissionsUseCase
// ═══════════════════════════════════════════════════════════════════════════════

describe('ListPermissionsUseCase', function (): void {
    it('lists all permissions grouped by resource', function (): void {
        $result = (new ListPermissionsUseCase)->execute();

        expect($result)->toBeInstanceOf(SupportCollection::class)
            ->and($result->has('properties'))->toBeTrue()
            ->and($result->has('roles'))->toBeTrue()
            ->and($result->get('properties'))->toHaveCount(2)
            ->and($result->get('roles'))->toHaveCount(1);
    });
});

// ═══════════════════════════════════════════════════════════════════════════════
// CreateAssignmentUseCase
// ═══════════════════════════════════════════════════════════════════════════════

describe('CreateAssignmentUseCase', function (): void {
    it('creates an assignment correctly', function (): void {
        $role = Role::create([
            'id' => '99999999-9999-9999-9999-999999999999',
            'name' => 'Assignable',
            'code' => 'assign_01',
            'level' => 'org',
            'organization_id' => ORG_ID,
            'is_system' => false,
            'is_active' => true,
        ]);

        $dto = new CreateAssignmentRequestDto(USER1_ID, $role->id, 'condominium', SCOPE_ID, null, null, ADMIN_ID);
        $result = (new CreateAssignmentUseCase)->execute($dto);

        expect($result)->toBeInstanceOf(RoleAssignment::class)
            ->and($result->user_id)->toBe(USER1_ID)
            ->and($result->role_id)->toBe($role->id)
            ->and($result->scope_type)->toBe('condominium')
            ->and($result->scope_id)->toBe(SCOPE_ID);
    });

    it('throws RoleNotFoundException when role does not exist', function (): void {
        $dto = new CreateAssignmentRequestDto(USER1_ID, '00000000-0000-0000-0000-000000000099', 'condominium', SCOPE_ID, null, null, ACTOR_ID);
        expect(fn () => (new CreateAssignmentUseCase)->execute($dto))
            ->toThrow(RoleNotFoundException::class);
    });

    it('throws AssignmentAlreadyExistsException when duplicate', function (): void {
        $role = Role::create([
            'id' => 'aaaaaaaa-1111-1111-1111-111111111111',
            'name' => 'DupRole',
            'code' => 'dup_01',
            'level' => 'org',
            'organization_id' => ORG_ID,
            'is_system' => false,
            'is_active' => true,
        ]);

        $dto = new CreateAssignmentRequestDto(USER2_ID, $role->id, 'condominium', SCOPE2_ID, null, null, ADMIN_ID);
        (new CreateAssignmentUseCase)->execute($dto);

        // Crear duplicado
        expect(fn () => (new CreateAssignmentUseCase)->execute($dto))
            ->toThrow(AssignmentAlreadyExistsException::class);
    });
});

// ═══════════════════════════════════════════════════════════════════════════════
// RevokeAssignmentUseCase
// ═══════════════════════════════════════════════════════════════════════════════

describe('RevokeAssignmentUseCase', function (): void {
    it('revokes an assignment correctly', function (): void {
        $role = Role::create([
            'id' => 'bbbbbbbb-1111-1111-1111-111111111111',
            'name' => 'RevokeRole',
            'code' => 'revoke_01',
            'level' => 'org',
            'organization_id' => ORG_ID,
            'is_system' => false,
            'is_active' => true,
        ]);

        $assignment = RoleAssignment::create([
            'id' => 'cccccccc-1111-1111-1111-111111111111',
            'user_id' => USER3_ID,
            'role_id' => $role->id,
            'scope_type' => 'condominium',
            'scope_id' => SCOPE3_ID,
            'assigned_by_user_id' => ADMIN_ID,
        ]);

        (new RevokeAssignmentUseCase)->execute($assignment->id, ACTOR_ID);

        // Verificar soft-delete
        $this->assertSoftDeleted('role_assignments', ['id' => $assignment->id]);
    });

    it('throws AssignmentNotFoundException when assignment does not exist', function (): void {
        expect(fn () => (new RevokeAssignmentUseCase)->execute('00000000-0000-0000-0000-000000000099', ACTOR_ID))
            ->toThrow(AssignmentNotFoundException::class);
    });
});

// ═══════════════════════════════════════════════════════════════════════════════
// CreateApprovalRuleUseCase
// ═══════════════════════════════════════════════════════════════════════════════

describe('CreateApprovalRuleUseCase', function (): void {
    it('creates an approval rule correctly', function (): void {
        $role = Role::create([
            'id' => 'dddddddd-1111-1111-1111-111111111111',
            'name' => 'Approver',
            'code' => 'approver_01',
            'level' => 'org',
            'organization_id' => ORG_ID,
            'is_system' => false,
            'is_active' => true,
        ]);

        $dto = new CreateApprovalRuleRequestDto('properties', 'write', ORG_ID, 5000.00, $role->id, true);
        $result = (new CreateApprovalRuleUseCase)->execute($dto, ACTOR_ID);

        expect($result->resource)->toBe('properties')
            ->and($result->action)->toBe('write')
            ->and($result->threshold)->toBe('5000.00')
            ->and($result->approver_role_id)->toBe($role->id)
            ->and($result->requires_second_approval)->toBeTrue();
    });

    it('throws ApprovalRuleInvalidApproverException when role does not exist', function (): void {
        $dto = new CreateApprovalRuleRequestDto('properties', 'write', ORG_ID, null, '00000000-0000-0000-0000-000000000099', false);
        expect(fn () => (new CreateApprovalRuleUseCase)->execute($dto, ACTOR_ID))
            ->toThrow(ApprovalRuleInvalidApproverException::class);
    });
});

// ═══════════════════════════════════════════════════════════════════════════════
// ListAuditLogUseCase
// ═══════════════════════════════════════════════════════════════════════════════

describe('ListAuditLogUseCase', function (): void {
    it('lists audit log with pagination', function (): void {
        $result = (new ListAuditLogUseCase)->execute(ORG_ID, [], 1, 20);

        expect($result)->toBeInstanceOf(LengthAwarePaginator::class);
    });

    it('respects date filters', function (): void {
        $result = (new ListAuditLogUseCase)->execute(ORG_ID, [
            'from' => '2026-01-01',
            'to' => '2026-12-31',
        ], 1, 20);

        expect($result)->toBeInstanceOf(LengthAwarePaginator::class);
    });
});
