<?php

declare(strict_types=1);

use App\Models\Condominium;
use App\Models\User;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Redis;
use Illuminate\Support\Str;
use Urbania\Auth\Application\Services\JwtServiceInterface;
use Urbania\Auth\Domain\ValueObjects\SessionId;

function condoMeToken(User $user): string
{
    $service = app(JwtServiceInterface::class);

    return $service->generateAccessToken(
        userId: $user->id,
        role: 'admin',
        mfaVerified: false,
        sessionId: SessionId::generate(),
        deviceFingerprint: '',
        organizationId: $user->organization_id,
    )->toString();
}

/**
 * Crea una organización adicional (distinta de la default de las factories).
 */
function createSecondaryOrganization(): string
{
    $id = (string) Str::orderedUuid();
    $now = now();

    DB::table('organizations')->insert([
        'id' => $id,
        'name' => 'Org Sin Condominio',
        'type' => 'edificio_unico',
        'nit' => '111111111-1',
        'email' => null,
        'country' => 'Colombia',
        'currency' => 'COP',
        'status' => 'activo',
        'logo_url' => null,
        'created_at' => $now,
        'updated_at' => $now,
    ]);

    return $id;
}

beforeEach(function (): void {
    Redis::flushall();
});

it('returns the condominium of the authenticated user organization', function (): void {
    $user = User::factory()->admin()->create();
    $condominium = Condominium::factory()->create();

    // Ambas factories usan la organización por defecto — misma org.
    expect($condominium->organization_id)->toBe($user->organization_id);

    $response = $this->withHeader('Authorization', 'Bearer '.condoMeToken($user))
        ->getJson('/api/v1/condominiums/me');

    $response->assertOk()
        ->assertJsonPath('data.id', $condominium->id)
        ->assertJsonStructure([
            'data' => ['id', 'name', 'address', 'city', 'department', 'country', 'nit', 'is_active'],
            'meta' => ['trace_id'],
        ]);
});

it('returns 404 when the user organization has no active condominium', function (): void {
    $orgId = createSecondaryOrganization();
    $user = User::factory()->admin()->create(['organization_id' => $orgId]);

    $response = $this->withHeader('Authorization', 'Bearer '.condoMeToken($user))
        ->getJson('/api/v1/condominiums/me');

    $response->assertNotFound()
        ->assertJsonPath('error.code', 'CONDOMINIUM_NOT_FOUND');
});

it('ignores inactive condominiums of the organization', function (): void {
    $orgId = createSecondaryOrganization();
    $user = User::factory()->admin()->create(['organization_id' => $orgId]);
    Condominium::factory()->create([
        'organization_id' => $orgId,
        'is_active' => false,
    ]);

    $response = $this->withHeader('Authorization', 'Bearer '.condoMeToken($user))
        ->getJson('/api/v1/condominiums/me');

    $response->assertNotFound()
        ->assertJsonPath('error.code', 'CONDOMINIUM_NOT_FOUND');
});

it('rejects unauthenticated requests', function (): void {
    $response = $this->getJson('/api/v1/condominiums/me');

    $response->assertUnauthorized();
});
