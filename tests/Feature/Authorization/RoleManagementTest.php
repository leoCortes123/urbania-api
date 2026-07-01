<?php

declare(strict_types=1);

use App\Models\Organization;
use App\Models\Role;
use App\Models\RoleAssignment;
use App\Models\User;
use Database\Seeders\RbacPermissionSeeder;
use Database\Seeders\RbacRoleSeeder;
use Illuminate\Foundation\Testing\LazilyRefreshDatabase;
use Urbania\Auth\Application\Services\JwtServiceInterface;
use Urbania\Auth\Domain\ValueObjects\SessionId;

uses(LazilyRefreshDatabase::class);

beforeEach(function (): void {
    $this->seed([
        RbacPermissionSeeder::class,
        RbacRoleSeeder::class,
    ]);

    $this->organization = Organization::factory()->create();

    $this->adminUser = User::factory()->create([
        'role' => 'admin',
        'status' => 'active',
        'organization_id' => $this->organization->id,
    ]);

    $adminRole = Role::where('code', 'admin')->firstOrFail();

    RoleAssignment::create([
        'user_id' => $this->adminUser->id,
        'role_id' => $adminRole->id,
        'scope_type' => 'organization',
        'scope_id' => $this->organization->id,
    ]);

    $this->token = app(JwtServiceInterface::class)->generateAccessToken(
        userId: $this->adminUser->id,
        role: $this->adminUser->role,
        mfaVerified: false,
        sessionId: SessionId::generate(),
        deviceFingerprint: '',
        organizationId: $this->organization->id,
    )->toString();
});

function authHeader(string $token): array
{
    return [
        'Authorization' => 'Bearer '.$token,
        'User-Agent' => '',
        'Accept-Language' => '',
        'X-Device-Name' => '',
    ];
}

it('lists roles scoped to the organization', function (): void {
    $response = $this->withHeaders(authHeader($this->token))
        ->getJson('/api/v1/authorization/roles');

    $response->assertOk()
        ->assertJsonStructure([
            'data' => [
                '*' => ['id', 'nombre', 'codigo', 'es_sistema', 'nivel_alcance', 'usuarios_count'],
            ],
            'meta' => ['trace_id'],
        ]);
});

it('creates a custom role for the organization', function (): void {
    $response = $this->withHeaders(authHeader($this->token))
        ->postJson('/api/v1/authorization/roles', [
            'nombre' => 'Rol de Prueba',
            'descripcion' => 'Rol creado en test',
            'nivel_alcance' => 'condominium',
        ]);

    $response->assertCreated()
        ->assertJsonPath('data.nombre', 'Rol de Prueba')
        ->assertJsonPath('data.nivel_alcance', 'condominium')
        ->assertJsonPath('data.es_sistema', false);
});

it('prevents editing system roles', function (): void {
    $systemRole = Role::where('is_system', true)->firstOrFail();

    $response = $this->withHeaders(authHeader($this->token))
        ->patchJson("/api/v1/authorization/roles/{$systemRole->id}", [
            'nombre' => 'Intento de edición',
        ]);

    $response->assertForbidden()
        ->assertJsonPath('error.code', 'ROLE_IS_SYSTEM');
});

it('updates a custom role', function (): void {
    $role = Role::create([
        'name' => 'Rol Editable',
        'code' => 'rol_editable',
        'organization_id' => $this->organization->id,
        'is_system' => false,
        'is_active' => true,
        'level' => 'organization',
    ]);

    $response = $this->withHeaders(authHeader($this->token))
        ->patchJson("/api/v1/authorization/roles/{$role->id}", [
            'nombre' => 'Rol Actualizado',
        ]);

    $response->assertOk()
        ->assertJsonPath('data.nombre', 'Rol Actualizado');
});

it('sets permissions for a custom role', function (): void {
    $role = Role::create([
        'name' => 'Rol Permisos',
        'code' => 'rol_permisos',
        'organization_id' => $this->organization->id,
        'is_system' => false,
        'is_active' => true,
        'level' => 'organization',
    ]);

    $response = $this->withHeaders(authHeader($this->token))
        ->putJson("/api/v1/authorization/roles/{$role->id}/permissions", [
            'permissions' => ['roles.ver', 'roles.crear'],
        ]);

    $response->assertOk()
        ->assertJsonPath('data.id', $role->id);
});

it('lists permissions grouped by resource', function (): void {
    $response = $this->withHeaders(authHeader($this->token))
        ->getJson('/api/v1/authorization/permissions');

    $response->assertOk()
        ->assertJsonStructure([
            'data' => [
                '*' => ['recurso', 'permisos'],
            ],
            'meta' => ['trace_id'],
        ]);
});

it('assigns a role to a user', function (): void {
    $targetUser = User::factory()->create([
        'organization_id' => $this->organization->id,
    ]);

    $role = Role::where('code', 'admin')->firstOrFail();

    $response = $this->withHeaders(authHeader($this->token))
        ->postJson('/api/v1/authorization/assignments', [
            'user_id' => $targetUser->id,
            'role_id' => $role->id,
            'scope_type' => 'organization',
            'scope_id' => $this->organization->id,
        ]);

    $response->assertCreated()
        ->assertJsonPath('data.user_id', $targetUser->id)
        ->assertJsonPath('data.role_id', $role->id);
});

it('revokes a role assignment', function (): void {
    $targetUser = User::factory()->create([
        'organization_id' => $this->organization->id,
    ]);

    $role = Role::where('code', 'admin')->firstOrFail();

    $assignment = RoleAssignment::create([
        'user_id' => $targetUser->id,
        'role_id' => $role->id,
        'scope_type' => 'organization',
        'scope_id' => $this->organization->id,
    ]);

    $response = $this->withHeaders(authHeader($this->token))
        ->deleteJson("/api/v1/authorization/assignments/{$assignment->id}");

    $response->assertNoContent();
});

it('creates an approval rule', function (): void {
    $approverRole = Role::where('code', 'admin')->firstOrFail();

    $response = $this->withHeaders(authHeader($this->token))
        ->postJson('/api/v1/authorization/approval-rules', [
            'resource' => 'cobranza',
            'action' => 'aprobar',
            'threshold' => 1000000.00,
            'approver_role_id' => $approverRole->id,
            'requires_second_approval' => true,
        ]);

    $response->assertCreated()
        ->assertJsonPath('data.resource', 'cobranza')
        ->assertJsonPath('data.threshold', '1000000.00')
        ->assertJsonPath('data.requires_second_approval', true);
});

it('lists audit log paginated', function (): void {
    $response = $this->withHeaders(authHeader($this->token))
        ->getJson('/api/v1/authorization/audit');

    $response->assertOk()
        ->assertJsonStructure([
            'data',
            'meta' => ['current_page', 'per_page', 'total', 'last_page', 'trace_id'],
        ]);
});

it('returns 404 when updating non-existent role', function (): void {
    $fakeId = '00000000-0000-0000-0000-000000000000';

    $response = $this->withHeaders(authHeader($this->token))
        ->patchJson("/api/v1/authorization/roles/{$fakeId}", [
            'nombre' => 'Rol Inexistente',
        ]);

    $response->assertNotFound()
        ->assertJsonPath('error.code', 'ROLE_NOT_FOUND');
});

it('returns 409 when creating role with duplicate name', function (): void {
    $this->withHeaders(authHeader($this->token))
        ->postJson('/api/v1/authorization/roles', [
            'nombre' => 'Rol Duplicado',
            'descripcion' => 'Rol creado en test',
            'nivel_alcance' => 'condominium',
        ])
        ->assertCreated();

    $response = $this->withHeaders(authHeader($this->token))
        ->postJson('/api/v1/authorization/roles', [
            'nombre' => 'Rol Duplicado',
            'descripcion' => 'Otro intento con mismo nombre',
            'nivel_alcance' => 'organization',
        ]);

    $response->assertConflict()
        ->assertJsonPath('error.code', 'ROLE_NAME_ALREADY_EXISTS');
});

it('returns 404 when setting permissions for non-existent role', function (): void {
    $fakeId = '00000000-0000-0000-0000-000000000000';

    $response = $this->withHeaders(authHeader($this->token))
        ->putJson("/api/v1/authorization/roles/{$fakeId}/permissions", [
            'permissions' => ['roles.ver'],
        ]);

    $response->assertNotFound()
        ->assertJsonPath('error.code', 'ROLE_NOT_FOUND');
});

it('returns 404 when revoking non-existent assignment', function (): void {
    $fakeId = '00000000-0000-0000-0000-000000000000';

    $response = $this->withHeaders(authHeader($this->token))
        ->deleteJson("/api/v1/authorization/assignments/{$fakeId}");

    $response->assertNotFound()
        ->assertJsonPath('error.code', 'ASSIGNMENT_NOT_FOUND');
});

it('returns 409 when creating duplicate assignment', function (): void {
    $targetUser = User::factory()->create([
        'organization_id' => $this->organization->id,
    ]);

    $role = Role::where('code', 'admin')->firstOrFail();

    $this->withHeaders(authHeader($this->token))
        ->postJson('/api/v1/authorization/assignments', [
            'user_id' => $targetUser->id,
            'role_id' => $role->id,
            'scope_type' => 'organization',
            'scope_id' => $this->organization->id,
        ])
        ->assertCreated();

    $response = $this->withHeaders(authHeader($this->token))
        ->postJson('/api/v1/authorization/assignments', [
            'user_id' => $targetUser->id,
            'role_id' => $role->id,
            'scope_type' => 'organization',
            'scope_id' => $this->organization->id,
        ]);

    $response->assertConflict()
        ->assertJsonPath('error.code', 'ASSIGNMENT_ALREADY_EXISTS');
});

it('returns 422 when creating approval rule with invalid approver role', function (): void {
    $response = $this->withHeaders(authHeader($this->token))
        ->postJson('/api/v1/authorization/approval-rules', [
            'resource' => 'cobranza',
            'action' => 'aprobar',
            'threshold' => 1000000.00,
            'approver_role_id' => '00000000-0000-0000-0000-000000000000',
            'requires_second_approval' => true,
        ]);

    $response->assertUnprocessable()
        ->assertJsonPath('error.code', 'VALIDATION_ERROR');
});
