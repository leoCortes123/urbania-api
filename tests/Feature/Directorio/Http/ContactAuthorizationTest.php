<?php

declare(strict_types=1);

use App\Models\User;
use Urbania\Auth\Application\Services\JwtServiceInterface;
use Urbania\Auth\Domain\ValueObjects\SessionId;

function generateDirectorioTokenFor(User $user): string
{
    $service = app(JwtServiceInterface::class);

    return $service->generateAccessToken(
        userId: $user->id,
        role: $user->role,
        mfaVerified: false,
        sessionId: SessionId::generate(),
        deviceFingerprint: '',
    )->toString();
}

beforeEach(function (): void {
    $this->withHeaders([
        'User-Agent' => '',
        'Accept-Language' => '',
        'X-Device-Name' => '',
    ]);
});

it('returns 401 for contacts without token', function (): void {
    $this->getJson('/api/v1/contacts')
        ->assertUnauthorized()
        ->assertJsonPath('error.code', 'TOKEN_INVALID');
});

it('returns 403 for contacts with user role token', function (): void {
    $user = User::factory()->create([
        'role' => 'user',
        'status' => 'active',
    ]);

    $this->withHeader('Authorization', 'Bearer '.generateDirectorioTokenFor($user))
        ->getJson('/api/v1/contacts')
        ->assertForbidden()
        ->assertJsonPath('error.code', 'FORBIDDEN');
});

it('returns 200 for contacts with admin role token', function (): void {
    $user = User::factory()->create([
        'role' => 'admin',
        'status' => 'active',
    ]);

    $this->withHeader('Authorization', 'Bearer '.generateDirectorioTokenFor($user))
        ->getJson('/api/v1/contacts')
        ->assertOk()
        ->assertJsonStructure([
            'data',
            'meta' => ['trace_id'],
        ]);
});
