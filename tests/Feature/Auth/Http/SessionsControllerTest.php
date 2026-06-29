<?php

declare(strict_types=1);

use App\Models\User;
use Illuminate\Foundation\Testing\LazilyRefreshDatabase;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Redis;
use Urbania\Auth\Application\Services\JwtServiceInterface;
use Urbania\Auth\Domain\ValueObjects\SessionId;

uses(LazilyRefreshDatabase::class);

function generateAccessTokenForSessions(User $user): string
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

beforeEach(function (): void {
    Cache::flush();
    Redis::flushall();
});

it('lists active sessions', function (): void {
    $user = User::factory()->create([
        'password_hash' => password_hash('Password123!', PASSWORD_ARGON2ID),
    ]);

    $this->postJson('/api/v1/auth/login', [
        'email' => $user->email,
        'password' => 'Password123!',
    ]);

    $token = generateAccessTokenForSessions($user);

    $response = $this->withHeader('Authorization', 'Bearer '.$token)
        ->getJson('/api/v1/auth/sessions');

    $response->assertOk()
        ->assertJsonStructure([
            'data' => [
                'sessions' => [
                    '*' => [
                        'session_id',
                        'device_name',
                        'device_fingerprint',
                        'ip_address',
                        'last_used_at',
                        'created_at',
                        'is_current',
                    ],
                ],
            ],
            'meta' => ['trace_id'],
        ]);
});

it('revokes all sessions except current', function (): void {
    $user = User::factory()->create([
        'password_hash' => password_hash('Password123!', PASSWORD_ARGON2ID),
    ]);

    $this->postJson('/api/v1/auth/login', [
        'email' => $user->email,
        'password' => 'Password123!',
    ]);

    $token = generateAccessTokenForSessions($user);

    $response = $this->withHeader('Authorization', 'Bearer '.$token)
        ->deleteJson('/api/v1/auth/sessions');

    $response->assertNoContent();
});

it('revokes a specific session', function (): void {
    $user = User::factory()->create([
        'password_hash' => password_hash('Password123!', PASSWORD_ARGON2ID),
    ]);

    $login = $this->postJson('/api/v1/auth/login', [
        'email' => $user->email,
        'password' => 'Password123!',
    ]);

    $accessToken = $login->json('data.access_token');
    $payload = app(JwtServiceInterface::class)->decode($accessToken);
    $otherSessionId = $payload['session_id'];

    $token = generateAccessTokenForSessions($user);

    $response = $this->withHeader('Authorization', 'Bearer '.$token)
        ->deleteJson('/api/v1/auth/sessions/'.$otherSessionId);

    $response->assertNoContent();
});

it('returns 404 when revoking unknown session', function (): void {
    $user = User::factory()->create([
        'password_hash' => password_hash('Password123!', PASSWORD_ARGON2ID),
    ]);
    $token = generateAccessTokenForSessions($user);

    $response = $this->withHeader('Authorization', 'Bearer '.$token)
        ->deleteJson('/api/v1/auth/sessions/'.SessionId::generate()->toString());

    $response->assertNotFound()
        ->assertJsonPath('error.code', 'SESSION_NOT_FOUND');
});
