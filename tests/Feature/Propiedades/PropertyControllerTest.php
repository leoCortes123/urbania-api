<?php

declare(strict_types=1);

use App\Models\Condominium;
use App\Models\Property;
use App\Models\PropertyStatus;
use App\Models\PropertyType;
use App\Models\Tower;
use App\Models\User;
use Database\Seeders\PropertyStatusSeeder;
use Database\Seeders\PropertyTypeSeeder;
use Illuminate\Support\Facades\Redis;
use Urbania\Auth\Application\Services\JwtServiceInterface;
use Urbania\Auth\Domain\ValueObjects\SessionId;

function propertyAdminToken(): string
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
    $this->seed([
        PropertyTypeSeeder::class,
        PropertyStatusSeeder::class,
    ]);
});

it('lists properties with pagination metadata', function (): void {
    $token = propertyAdminToken();
    Property::factory()->count(3)->create();

    $response = $this->withHeader('Authorization', "Bearer {$token}")
        ->getJson('/api/v1/properties');

    $response->assertOk()
        ->assertJsonStructure([
            'data' => [
                '*' => ['id', 'condominium_id', 'tower_id', 'property_type_id', 'property_status_id', 'floor', 'unit_number', 'area_m2', 'coefficient'],
            ],
            'meta' => ['trace_id', 'current_page', 'per_page', 'total', 'last_page'],
        ]);
});

it('creates a property', function (): void {
    $token = propertyAdminToken();
    $condominium = Condominium::factory()->create();
    $tower = Tower::factory()->create(['condominium_id' => $condominium->id, 'floor_count' => 10]);
    $type = PropertyType::first();
    $status = PropertyStatus::where('code', 'vacia')->first();

    $response = $this->withHeader('Authorization', "Bearer {$token}")
        ->postJson('/api/v1/properties', [
            'tower_id' => $tower->id,
            'property_type_id' => $type->id,
            'property_status_id' => $status->id,
            'floor' => 5,
            'unit_number' => '501',
            'area_m2' => '120.50',
            'coefficient' => '0.125000',
        ]);

    $response->assertCreated()
        ->assertJsonPath('data.floor', 5)
        ->assertJsonPath('data.unit_number', '501');
});

it('returns 409 when creating property with duplicate unit', function (): void {
    $token = propertyAdminToken();
    $tower = Tower::factory()->create(['floor_count' => 5]);
    $property = Property::factory()->create([
        'tower_id' => $tower->id,
        'condominium_id' => $tower->condominium_id,
        'floor' => 2,
        'unit_number' => '201',
    ]);

    $response = $this->withHeader('Authorization', "Bearer {$token}")
        ->postJson('/api/v1/properties', [
            'tower_id' => $property->tower_id,
            'property_type_id' => $property->property_type_id,
            'floor' => $property->floor,
            'unit_number' => $property->unit_number,
            'area_m2' => '100.00',
            'coefficient' => '0.100000',
        ]);

    $response->assertStatus(409)
        ->assertJsonPath('error.code', 'PROPERTY_DUPLICATE_UNIT');
});

it('shows a property by id', function (): void {
    $token = propertyAdminToken();
    $property = Property::factory()->create();

    $response = $this->withHeader('Authorization', "Bearer {$token}")
        ->getJson("/api/v1/properties/{$property->id}");

    $response->assertOk()
        ->assertJsonPath('data.id', $property->id)
        ->assertJsonStructure(['data' => ['tower', 'type', 'status', 'full_designation', 'residents_count', 'documents_count']]);
});

it('updates a property', function (): void {
    $token = propertyAdminToken();
    $tower = Tower::factory()->create(['floor_count' => 30]);
    $property = Property::factory()->create(['tower_id' => $tower->id, 'condominium_id' => $tower->condominium_id, 'floor' => 5]);

    $response = $this->withHeader('Authorization', "Bearer {$token}")
        ->patchJson("/api/v1/properties/{$property->id}", [
            'unit_number' => 'Updated Unit',
            'area_m2' => '150.00',
        ]);

    $response->assertOk()
        ->assertJsonPath('data.unit_number', 'Updated Unit')
        ->assertJsonPath('data.area_m2', '150.00');
});

it('deletes a property', function (): void {
    $token = propertyAdminToken();
    $property = Property::factory()->create();

    $response = $this->withHeader('Authorization', "Bearer {$token}")
        ->deleteJson("/api/v1/properties/{$property->id}");

    $response->assertNoContent();
    expect(Property::find($property->id))->toBeNull();
});

it('changes property status', function (): void {
    $token = propertyAdminToken();
    $property = Property::factory()->create();
    $newStatus = PropertyStatus::where('code', 'ocupada')->first();

    $response = $this->withHeader('Authorization', "Bearer {$token}")
        ->patchJson("/api/v1/properties/{$property->id}/status", [
            'property_status_id' => $newStatus->id,
            'reason' => 'Cambio por nuevo residente',
        ]);

    $response->assertOk()
        ->assertJsonPath('data.property_status_id', $newStatus->id);
});

it('returns 400 when changing to same status', function (): void {
    $token = propertyAdminToken();
    $property = Property::factory()->create();

    $response = $this->withHeader('Authorization', "Bearer {$token}")
        ->patchJson("/api/v1/properties/{$property->id}/status", [
            'property_status_id' => $property->property_status_id,
            'reason' => 'Mismo estado',
        ]);

    $response->assertStatus(400)
        ->assertJsonPath('error.code', 'SAME_STATUS');
});

it('returns status log for a property', function (): void {
    $token = propertyAdminToken();
    $property = Property::factory()->create();

    $response = $this->withHeader('Authorization', "Bearer {$token}")
        ->getJson("/api/v1/properties/{$property->id}/status-log");

    $response->assertOk()
        ->assertJsonStructure([
            'data' => [],
            'meta' => ['trace_id', 'current_page', 'per_page', 'total', 'last_page'],
        ]);
});
