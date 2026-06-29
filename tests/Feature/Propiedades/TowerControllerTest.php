<?php

declare(strict_types=1);

use App\Models\Condominium;
use App\Models\Property;
use App\Models\Tower;
use App\Models\User;
use Illuminate\Foundation\Testing\LazilyRefreshDatabase;
use Illuminate\Support\Facades\Redis;
use Urbania\Auth\Application\Services\JwtServiceInterface;
use Urbania\Auth\Domain\ValueObjects\SessionId;

uses(LazilyRefreshDatabase::class);

function towerAdminToken(): string
{
    $user = User::factory()->admin()->create();
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

beforeEach(function (): void {
    Redis::flushall();
});

it('lists towers by condominium', function (): void {
    $token = towerAdminToken();
    $condominium = Condominium::factory()->create();
    Tower::factory()->create(['condominium_id' => $condominium->id, 'name' => 'Torre A']);
    Tower::factory()->create(['condominium_id' => $condominium->id, 'name' => 'Torre B']);
    Tower::factory()->create(['condominium_id' => $condominium->id, 'name' => 'Torre C']);

    $response = $this->withHeader('Authorization', "Bearer {$token}")
        ->getJson("/api/v1/condominiums/{$condominium->id}/towers");

    $response->assertOk()
        ->assertJsonCount(3, 'data')
        ->assertJsonStructure([
            'data' => [
                '*' => ['id', 'condominium_id', 'name', 'code', 'floor_count', 'has_elevator', 'sort_order'],
            ],
            'meta' => ['trace_id', 'current_page', 'per_page', 'total', 'last_page'],
        ]);
});

it('creates a tower', function (): void {
    $token = towerAdminToken();
    $condominium = Condominium::factory()->create();

    $response = $this->withHeader('Authorization', "Bearer {$token}")
        ->postJson('/api/v1/towers', [
            'condominium_id' => $condominium->id,
            'name' => 'Torre Central',
            'code' => 'TC',
            'floor_count' => 10,
            'has_elevator' => true,
            'sort_order' => 1,
        ]);

    $response->assertCreated()
        ->assertJsonPath('data.name', 'Torre Central')
        ->assertJsonPath('data.floor_count', 10);
});

it('returns 409 when creating tower with duplicate name in condominium', function (): void {
    $token = towerAdminToken();
    $condominium = Condominium::factory()->create();
    Tower::factory()->create(['condominium_id' => $condominium->id, 'name' => 'Torre Central']);

    $response = $this->withHeader('Authorization', "Bearer {$token}")
        ->postJson('/api/v1/towers', [
            'condominium_id' => $condominium->id,
            'name' => 'Torre Central',
            'floor_count' => 5,
        ]);

    $response->assertStatus(409)
        ->assertJsonPath('error.code', 'TOWER_NAME_ALREADY_EXISTS');
});

it('shows a tower by id', function (): void {
    $token = towerAdminToken();
    $tower = Tower::factory()->create();

    $response = $this->withHeader('Authorization', "Bearer {$token}")
        ->getJson("/api/v1/towers/{$tower->id}");

    $response->assertOk()
        ->assertJsonPath('data.id', $tower->id);
});

it('updates a tower', function (): void {
    $token = towerAdminToken();
    $tower = Tower::factory()->create();

    $response = $this->withHeader('Authorization', "Bearer {$token}")
        ->patchJson("/api/v1/towers/{$tower->id}", [
            'name' => 'Updated Tower',
            'floor_count' => 12,
        ]);

    $response->assertOk()
        ->assertJsonPath('data.name', 'Updated Tower')
        ->assertJsonPath('data.floor_count', 12);
});

it('deletes a tower', function (): void {
    $token = towerAdminToken();
    $tower = Tower::factory()->create();

    $response = $this->withHeader('Authorization', "Bearer {$token}")
        ->deleteJson("/api/v1/towers/{$tower->id}");

    $response->assertNoContent();
    expect(Tower::find($tower->id))->toBeNull();
});

it('returns 409 when deleting tower with properties', function (): void {
    $token = towerAdminToken();
    $tower = Tower::factory()->create();
    Property::factory()->create(['tower_id' => $tower->id, 'condominium_id' => $tower->condominium_id]);

    $response = $this->withHeader('Authorization', "Bearer {$token}")
        ->deleteJson("/api/v1/towers/{$tower->id}");

    $response->assertStatus(409)
        ->assertJsonPath('error.code', 'TOWER_HAS_PROPERTIES');
});
