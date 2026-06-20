<?php

declare(strict_types=1);

use App\Models\User;
use Illuminate\Foundation\Testing\LazilyRefreshDatabase;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Redis;
use Tests\TestCase;

uses(TestCase::class);
uses(LazilyRefreshDatabase::class);

beforeEach(function (): void {
    Cache::flush();
    Redis::flushall();
});

it('detects replay of a rotated refresh token and revokes the token family', function (): void {
    $user = User::factory()->create([
        'password_hash' => password_hash('Password123!', PASSWORD_ARGON2ID),
    ]);

    $login = $this->withHeaders([
        'User-Agent' => 'Mozilla/5.0 Replay Device',
        'Accept-Language' => '',
        'X-Device-Name' => 'Replay Device',
    ])->postJson('/api/v1/auth/login', [
        'email' => $user->email,
        'password' => 'Password123!',
    ]);

    $originalRefreshToken = $login->json('data.refresh_token');

    $refresh = $this->withHeaders([
        'User-Agent' => 'Mozilla/5.0 Replay Device',
        'Accept-Language' => '',
        'X-Device-Name' => 'Replay Device',
    ])->postJson('/api/v1/auth/refresh', [
        'refresh_token' => $originalRefreshToken,
    ]);

    $refresh->assertOk();

    $newRefreshToken = $refresh->json('data.refresh_token');

    $replay = $this->withHeaders([
        'User-Agent' => 'Mozilla/5.0 Replay Device',
        'Accept-Language' => '',
        'X-Device-Name' => 'Replay Device',
    ])->postJson('/api/v1/auth/refresh', [
        'refresh_token' => $originalRefreshToken,
    ]);

    $replay->assertUnauthorized()
        ->assertJsonPath('error.code', 'TOKEN_INVALID');

    $familyRevoked = $this->withHeaders([
        'User-Agent' => 'Mozilla/5.0 Replay Device',
        'Accept-Language' => '',
        'X-Device-Name' => 'Replay Device',
    ])->postJson('/api/v1/auth/refresh', [
        'refresh_token' => $newRefreshToken,
    ]);

    $familyRevoked->assertUnauthorized()
        ->assertJsonPath('error.code', 'TOKEN_INVALID');
});

it('returns 403 when refresh token is used from a different device fingerprint', function (): void {
    $user = User::factory()->create([
        'password_hash' => password_hash('Password123!', PASSWORD_ARGON2ID),
    ]);

    $login = $this->withHeader('User-Agent', 'Mozilla/5.0 Original Device')
        ->postJson('/api/v1/auth/login', [
            'email' => $user->email,
            'password' => 'Password123!',
        ]);

    $refreshToken = $login->json('data.refresh_token');

    $refresh = $this->withHeader('User-Agent', 'Mozilla/5.0 Different Device')
        ->postJson('/api/v1/auth/refresh', [
            'refresh_token' => $refreshToken,
        ]);

    $refresh->assertForbidden()
        ->assertJsonPath('error.code', 'DEVICE_NOT_RECOGNIZED');
});
