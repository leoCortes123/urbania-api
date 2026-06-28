<?php

declare(strict_types=1);

use App\Models\Property;
use App\Models\PropertyStatus;
use App\Models\User;
use Illuminate\Support\Facades\Redis;
use Illuminate\Support\Str;
use Urbania\Auth\Application\Services\JwtServiceInterface;
use Urbania\Auth\Domain\ValueObjects\SessionId;

function createAdminTokenForStatus(): string
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

function createUserTokenForStatus(): string
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

it('lists property statuses with pagination metadata', function (): void {
    $token = createAdminTokenForStatus();
    PropertyStatus::factory()->count(3)->create();

    $response = $this->withHeader('Authorization', "Bearer {$token}")
        ->getJson('/api/v1/property-statuses');

    $response->assertOk()
        ->assertJsonStructure([
            'data' => [
                '*' => ['id', 'code', 'name', 'description', 'allows_residents', 'is_active', 'sort_order', 'created_at', 'updated_at'],
            ],
            'meta' => ['trace_id', 'current_page', 'per_page', 'total', 'last_page'],
        ]);
});

it('creates a property status', function (): void {
    $token = createAdminTokenForStatus();

    $response = $this->withHeader('Authorization', "Bearer {$token}")
        ->postJson('/api/v1/property-statuses', [
            'code' => 'nuevo_estado',
            'name' => 'Nuevo Estado',
            'description' => 'Descripción',
            'allows_residents' => false,
            'sort_order' => 5,
        ]);

    $response->assertCreated()
        ->assertJsonPath('data.code', 'nuevo_estado')
        ->assertJsonPath('data.allows_residents', false)
        ->assertJsonPath('data.is_active', true);
});

it('returns 409 when creating property status with duplicate code', function (): void {
    $token = createAdminTokenForStatus();
    PropertyStatus::factory()->create(['code' => 'duplicado']);

    $response = $this->withHeader('Authorization', "Bearer {$token}")
        ->postJson('/api/v1/property-statuses', [
            'code' => 'duplicado',
            'name' => 'Otro',
        ]);

    $response->assertStatus(409)
        ->assertJsonPath('error.code', 'PROPERTY_STATUS_CODE_ALREADY_EXISTS');
});

it('updates a property status', function (): void {
    $token = createAdminTokenForStatus();
    $status = PropertyStatus::factory()->create(['name' => 'Old Name']);

    $response = $this->withHeader('Authorization', "Bearer {$token}")
        ->patchJson("/api/v1/property-statuses/{$status->id}", [
            'name' => 'New Name',
            'allows_residents' => true,
            'sort_order' => 10,
        ]);

    $response->assertOk()
        ->assertJsonPath('data.name', 'New Name')
        ->assertJsonPath('data.allows_residents', true)
        ->assertJsonPath('data.sort_order', 10);
});

it('returns 404 when updating unknown property status', function (): void {
    $token = createAdminTokenForStatus();

    $response = $this->withHeader('Authorization', "Bearer {$token}")
        ->patchJson('/api/v1/property-statuses/'.Str::orderedUuid(), [
            'name' => 'New Name',
        ]);

    $response->assertNotFound()
        ->assertJsonPath('error.code', 'PROPERTY_STATUS_NOT_FOUND');
});

it('deactivates a property status', function (): void {
    $token = createAdminTokenForStatus();
    $status = PropertyStatus::factory()->create();

    $response = $this->withHeader('Authorization', "Bearer {$token}")
        ->deleteJson("/api/v1/property-statuses/{$status->id}");

    $response->assertNoContent();
    expect(PropertyStatus::find($status->id)->is_active)->toBeFalse();
});

it('returns 409 when deleting a property status in use', function (): void {
    $token = createAdminTokenForStatus();
    $status = PropertyStatus::factory()->create();
    Property::factory()->create(['property_status_id' => $status->id]);

    $response = $this->withHeader('Authorization', "Bearer {$token}")
        ->deleteJson("/api/v1/property-statuses/{$status->id}");

    $response->assertStatus(409)
        ->assertJsonPath('error.code', 'PROPERTY_STATUS_IN_USE');
});

it('returns 409 when deleting seeded property status', function (): void {
    $token = createAdminTokenForStatus();
    $status = PropertyStatus::factory()->create(['code' => 'ocupada']);

    $response = $this->withHeader('Authorization', "Bearer {$token}")
        ->deleteJson("/api/v1/property-statuses/{$status->id}");

    $response->assertStatus(409)
        ->assertJsonPath('error.code', 'PROPERTY_STATUS_IN_USE');
});

it('denies access to non-admin users', function (): void {
    $token = createUserTokenForStatus();

    $response = $this->withHeader('Authorization', "Bearer {$token}")
        ->getJson('/api/v1/property-statuses');

    $response->assertForbidden()
        ->assertJsonPath('error.code', 'FORBIDDEN');
});

it('denies access without token', function (): void {
    $response = $this->getJson('/api/v1/property-statuses');

    $response->assertUnauthorized();
});
