<?php

declare(strict_types=1);

use App\Models\Property;
use App\Models\PropertyType;
use App\Models\User;
use Illuminate\Support\Facades\Redis;
use Illuminate\Support\Str;
use Urbania\Auth\Application\Services\JwtServiceInterface;
use Urbania\Auth\Domain\ValueObjects\SessionId;

function createAdminToken(): string
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

function createUserToken(): string
{
    $user = User::factory()->create();
    $service = app(JwtServiceInterface::class);

    return $service->generateAccessToken(
        userId: $user->id,
        role: 'user',
        mfaVerified: false,
        sessionId: SessionId::generate(),
        deviceFingerprint: '',
    )->toString();
}

beforeEach(function (): void {
    Redis::flushall();
});

it('lists property types with pagination metadata', function (): void {
    $token = createAdminToken();
    PropertyType::factory()->count(3)->create();

    $response = $this->withHeader('Authorization', "Bearer {$token}")
        ->getJson('/api/v1/property-types');

    $response->assertOk()
        ->assertJsonStructure([
            'data' => [
                '*' => ['id', 'code', 'name', 'description', 'sort_order', 'is_active', 'created_at', 'updated_at'],
            ],
            'meta' => ['trace_id', 'current_page', 'per_page', 'total', 'last_page'],
        ])
        ->assertJsonPath('meta.current_page', 1)
        ->assertJsonPath('meta.per_page', 20);
});

it('filters property types by search term', function (): void {
    $token = createAdminToken();
    PropertyType::factory()->create(['code' => 'xyz', 'name' => 'Unique Name']);
    PropertyType::factory()->count(2)->create();

    $response = $this->withHeader('Authorization', "Bearer {$token}")
        ->getJson('/api/v1/property-types?search=Unique');

    $response->assertOk()
        ->assertJsonCount(1, 'data')
        ->assertJsonPath('data.0.name', 'Unique Name');
});

it('filters property types by active status', function (): void {
    $token = createAdminToken();
    PropertyType::factory()->create(['is_active' => false]);
    PropertyType::factory()->count(2)->create(['is_active' => true]);

    $response = $this->withHeader('Authorization', "Bearer {$token}")
        ->getJson('/api/v1/property-types?is_active=0');

    $response->assertOk()
        ->assertJsonCount(1, 'data')
        ->assertJsonPath('data.0.is_active', false);
});

it('creates a property type', function (): void {
    $token = createAdminToken();

    $response = $this->withHeader('Authorization', "Bearer {$token}")
        ->postJson('/api/v1/property-types', [
            'code' => 'nuevo_tipo',
            'name' => 'Nuevo Tipo',
            'description' => 'Descripción',
            'sort_order' => 5,
        ]);

    $response->assertCreated()
        ->assertJsonPath('data.code', 'nuevo_tipo')
        ->assertJsonPath('data.name', 'Nuevo Tipo')
        ->assertJsonPath('data.is_active', true);
});

it('returns 409 when creating property type with duplicate code', function (): void {
    $token = createAdminToken();
    PropertyType::factory()->create(['code' => 'duplicado']);

    $response = $this->withHeader('Authorization', "Bearer {$token}")
        ->postJson('/api/v1/property-types', [
            'code' => 'duplicado',
            'name' => 'Otro',
        ]);

    $response->assertStatus(409)
        ->assertJsonPath('error.code', 'PROPERTY_TYPE_CODE_ALREADY_EXISTS');
});

it('updates a property type', function (): void {
    $token = createAdminToken();
    $type = PropertyType::factory()->create(['name' => 'Old Name']);

    $response = $this->withHeader('Authorization', "Bearer {$token}")
        ->patchJson("/api/v1/property-types/{$type->id}", [
            'name' => 'New Name',
            'sort_order' => 10,
        ]);

    $response->assertOk()
        ->assertJsonPath('data.name', 'New Name')
        ->assertJsonPath('data.sort_order', 10);
});

it('returns 404 when updating unknown property type', function (): void {
    $token = createAdminToken();

    $response = $this->withHeader('Authorization', "Bearer {$token}")
        ->patchJson('/api/v1/property-types/'.Str::orderedUuid(), [
            'name' => 'New Name',
        ]);

    $response->assertNotFound()
        ->assertJsonPath('error.code', 'PROPERTY_TYPE_NOT_FOUND');
});

it('deactivates a property type', function (): void {
    $token = createAdminToken();
    $type = PropertyType::factory()->create();

    $response = $this->withHeader('Authorization', "Bearer {$token}")
        ->deleteJson("/api/v1/property-types/{$type->id}");

    $response->assertNoContent();
    expect(PropertyType::find($type->id)->is_active)->toBeFalse();
});

it('returns 409 when deleting a property type in use', function (): void {
    $token = createAdminToken();
    $type = PropertyType::factory()->create();
    Property::factory()->create(['property_type_id' => $type->id]);

    $response = $this->withHeader('Authorization', "Bearer {$token}")
        ->deleteJson("/api/v1/property-types/{$type->id}");

    $response->assertStatus(409)
        ->assertJsonPath('error.code', 'PROPERTY_TYPE_IN_USE');
});

it('returns 409 when deleting seeded property type', function (): void {
    $token = createAdminToken();
    $type = PropertyType::factory()->create(['code' => 'apartamento']);

    $response = $this->withHeader('Authorization', "Bearer {$token}")
        ->deleteJson("/api/v1/property-types/{$type->id}");

    $response->assertStatus(409)
        ->assertJsonPath('error.code', 'PROPERTY_TYPE_IN_USE');
});

it('denies access to non-admin users', function (): void {
    $token = createUserToken();

    $response = $this->withHeader('Authorization', "Bearer {$token}")
        ->getJson('/api/v1/property-types');

    $response->assertForbidden()
        ->assertJsonPath('error.code', 'FORBIDDEN');
});

it('denies access without token', function (): void {
    $response = $this->getJson('/api/v1/property-types');

    $response->assertUnauthorized();
});
