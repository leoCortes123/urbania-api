<?php

declare(strict_types=1);

use App\Models\Organization;
use App\Models\User;
use Illuminate\Foundation\Testing\LazilyRefreshDatabase;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Redis;
use Urbania\Auth\Application\Services\JwtServiceInterface;
use Urbania\Auth\Domain\ValueObjects\SessionId;

uses(LazilyRefreshDatabase::class);

beforeEach(function (): void {
    Redis::flushall();
});

function tenancyTokenFor(User $user): string
{
    $service = app(JwtServiceInterface::class);

    return $service->generateAccessToken(
        userId: $user->id,
        role: $user->role,
        mfaVerified: false,
        sessionId: SessionId::generate(),
        deviceFingerprint: '',
        organizationId: $user->organization_id,
    )->toString();
}

it('creates an organization via model', function (): void {
    $organization = Organization::factory()->create([
        'name' => 'Acme Towers',
        'type' => 'edificio_unico',
        'status' => 'activo',
    ]);

    expect($organization)
        ->toBeInstanceOf(Organization::class)
        ->and($organization->name)->toBe('Acme Towers')
        ->and($organization->status)->toBe('activo')
        ->and($organization->country)->toBe('Colombia')
        ->and($organization->currency)->toBe('COP');
});

it('isolates organization data by organization_id', function (): void {
    $organizationA = Organization::factory()->create();
    $organizationB = Organization::factory()->create();

    $userA = User::factory()->create(['organization_id' => $organizationA->id]);
    $userB = User::factory()->create(['organization_id' => $organizationB->id]);

    expect($userA->organization_id)->toBe($organizationA->id)
        ->and($userB->organization_id)->toBe($organizationB->id)
        ->and($userA->organization_id)->not->toBe($userB->organization_id);
});

it('allows public health endpoint without tenant context', function (): void {
    $response = $this->getJson('/api/v1/health');

    $response->assertOk()
        ->assertJsonPath('data.status', 'healthy');
});

it('rejects protected endpoint without bearer token', function (): void {
    $response = $this->getJson('/api/v1/auth/me');

    $response->assertUnauthorized()
        ->assertJsonPath('error.code', 'TENANT_REQUIRED');
});

it('rejects protected endpoint with suspended organization', function (): void {
    $organization = Organization::factory()->suspended()->create();
    $user = User::factory()->create(['organization_id' => $organization->id]);
    $token = tenancyTokenFor($user);

    $response = $this->withHeader('Authorization', "Bearer {$token}")
        ->getJson('/api/v1/auth/me');

    $response->assertForbidden()
        ->assertJsonPath('error.code', 'TENANT_SUSPENDED');
});

it('allows protected endpoint with active organization', function (): void {
    $organization = Organization::factory()->active()->create();
    $user = User::factory()->create([
        'organization_id' => $organization->id,
        'password_hash' => password_hash('Password123!', PASSWORD_ARGON2ID),
    ]);
    $token = tenancyTokenFor($user);

    $response = $this->withHeader('Authorization', "Bearer {$token}")
        ->getJson('/api/v1/auth/me');

    $response->assertOk()
        ->assertJsonPath('data.id', $user->id);
});

it('sets app.org_id in database session for active organization', function (): void {
    $organization = Organization::factory()->active()->create();
    $user = User::factory()->create([
        'organization_id' => $organization->id,
        'password_hash' => password_hash('Password123!', PASSWORD_ARGON2ID),
    ]);
    $token = tenancyTokenFor($user);

    DB::listen(function ($query): void {
        if (str_contains($query->sql, 'SET LOCAL app.org_id')) {
            $this->tenantSetLocalExecuted = true;
        }
    });

    $this->tenantSetLocalExecuted = false;

    $this->withHeader('Authorization', "Bearer {$token}")
        ->getJson('/api/v1/auth/me');

    expect($this->tenantSetLocalExecuted)->toBeTrue();
});

it('includes org_id claim in access token after login', function (): void {
    $organization = Organization::factory()->active()->create();
    $user = User::factory()->create([
        'email' => 'tenant-login@example.com',
        'organization_id' => $organization->id,
        'password_hash' => password_hash('Password123!', PASSWORD_ARGON2ID),
    ]);

    $response = $this->postJson('/api/v1/auth/login', [
        'email' => 'tenant-login@example.com',
        'password' => 'Password123!',
    ]);

    $response->assertOk();

    $accessToken = $response->json('data.access_token');
    $service = app(JwtServiceInterface::class);
    $decoded = $service->decode($accessToken);

    expect($decoded)->toHaveKey('org_id')
        ->and($decoded['org_id'])->toBe($organization->id);
});
