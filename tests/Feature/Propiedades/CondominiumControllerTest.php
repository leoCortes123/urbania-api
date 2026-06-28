<?php

declare(strict_types=1);

use App\Models\Condominium;
use App\Models\User;
use Illuminate\Support\Facades\Redis;
use Urbania\Auth\Application\Services\JwtServiceInterface;
use Urbania\Auth\Domain\ValueObjects\SessionId;

function condoAdminToken(): string
{
    $user = User::factory()->admin()->create();
    $service = app(JwtServiceInterface::class);

    return $service->generateAccessToken(
        userId: $user->id,
        role: 'admin',
        mfaVerified: false,
        sessionId: SessionId::generate(),
        deviceFingerprint: '',
    )->toString();
}

beforeEach(function (): void {
    Redis::flushall();
});

it('lists condominiums with pagination metadata', function (): void {
    $token = condoAdminToken();
    Condominium::factory()->count(3)->create();

    $response = $this->withHeader('Authorization', "Bearer {$token}")
        ->getJson('/api/v1/condominiums');

    $response->assertOk()
        ->assertJsonStructure([
            'data' => [
                '*' => ['id', 'name', 'address', 'city', 'department', 'country', 'nit', 'phone', 'email', 'legal_representative', 'total_coefficient', 'logo_url', 'is_active', 'created_at', 'updated_at'],
            ],
            'meta' => ['trace_id', 'current_page', 'per_page', 'total', 'last_page'],
        ]);
});

it('shows a condominium by id', function (): void {
    $token = condoAdminToken();
    $condominium = Condominium::factory()->create();

    $response = $this->withHeader('Authorization', "Bearer {$token}")
        ->getJson("/api/v1/condominiums/{$condominium->id}");

    $response->assertOk()
        ->assertJsonPath('data.id', $condominium->id);
});

it('updates a condominium', function (): void {
    $token = condoAdminToken();
    $condominium = Condominium::factory()->create();

    $response = $this->withHeader('Authorization', "Bearer {$token}")
        ->patchJson("/api/v1/condominiums/{$condominium->id}", [
            'name' => 'Updated Name',
            'city' => 'Medellín',
        ]);

    $response->assertOk()
        ->assertJsonPath('data.name', 'Updated Name')
        ->assertJsonPath('data.city', 'Medellín');
});

it('validates coefficients for a condominium', function (): void {
    $token = condoAdminToken();
    $condominium = Condominium::factory()->create(['total_coefficient' => '1.000000']);

    $response = $this->withHeader('Authorization', "Bearer {$token}")
        ->getJson("/api/v1/condominiums/{$condominium->id}/coefficient-validation");

    $response->assertOk()
        ->assertJsonStructure([
            'data' => ['total_expected', 'total_actual', 'difference', 'is_balanced', 'unit_count'],
            'meta' => ['trace_id'],
        ]);
});
