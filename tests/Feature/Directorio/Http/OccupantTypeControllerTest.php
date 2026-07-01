<?php

declare(strict_types=1);

use App\Models\User;
use Database\Seeders\DirectorioSeeder;
use Illuminate\Support\Facades\Redis;
use Urbania\Auth\Application\Services\JwtServiceInterface;
use Urbania\Auth\Domain\ValueObjects\SessionId;

function directorioOccupantAdminToken(): string
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

it('lists occupant types', function (): void {
    $token = directorioOccupantAdminToken();
    $this->seed(DirectorioSeeder::class);

    $response = $this->withHeader('Authorization', "Bearer {$token}")
        ->getJson('/api/v1/occupant-types');

    $response->assertOk()
        ->assertJsonStructure([
            'data' => [
                '*' => ['id', 'code', 'name', 'sort_order'],
            ],
            'meta' => ['trace_id'],
        ])
        ->assertJsonCount(6, 'data');
});

it('lists occupant types without token returns 401', function (): void {
    $this->seed(DirectorioSeeder::class);

    $this->getJson('/api/v1/occupant-types')
        ->assertUnauthorized()
        ->assertJsonPath('error.code', 'TENANT_REQUIRED');
});
