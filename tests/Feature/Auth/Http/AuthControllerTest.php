<?php

declare(strict_types=1);

use App\Models\User;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Redis;
use Urbania\Auth\Application\Services\JwtServiceInterface;
use Urbania\Auth\Domain\ValueObjects\SessionId;

function generateTokenFor(User $user): string
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
    Cache::flush();
    Redis::flushall();

    $this->withHeaders([
        'User-Agent' => '',
        'Accept-Language' => '',
        'X-Device-Name' => '',
    ]);
});

it('logs in a user and returns tokens with user data', function (): void {
    $user = User::factory()->create([
        'email' => 'login@example.com',
        'password_hash' => password_hash('Password123!', PASSWORD_ARGON2ID),
    ]);

    $response = $this->postJson('/api/v1/auth/login', [
        'email' => 'login@example.com',
        'password' => 'Password123!',
    ]);

    $response->assertOk()
        ->assertJsonStructure([
            'data' => [
                'access_token',
                'refresh_token',
                'token_type',
                'expires_in',
                'user' => [
                    'id',
                    'name',
                    'email',
                    'role',
                    'status',
                ],
            ],
            'meta' => ['trace_id'],
        ])
        ->assertJsonPath('data.user.email', $user->email)
        ->assertJsonPath('data.token_type', 'bearer')
        ->assertJsonPath('data.expires_in', 900);

    expect($response->headers->get('X-Trace-Id'))->toBe($response->json('meta.trace_id'));
    expect($response->headers->get('Strict-Transport-Security'))->toBe('max-age=31536000; includeSubDomains; preload');
});

it('returns 401 for invalid credentials', function (): void {
    $response = $this->postJson('/api/v1/auth/login', [
        'email' => 'missing@example.com',
        'password' => 'Password123!',
    ]);

    $response->assertUnauthorized()
        ->assertJsonStructure(['error' => ['code', 'message', 'trace_id']])
        ->assertJsonPath('error.code', 'INVALID_CREDENTIALS');
});

it('returns 401 when account is locked', function (): void {
    User::factory()->locked()->create([
        'email' => 'locked@example.com',
        'password_hash' => password_hash('Password123!', PASSWORD_ARGON2ID),
    ]);

    $response = $this->postJson('/api/v1/auth/login', [
        'email' => 'locked@example.com',
        'password' => 'Password123!',
    ]);

    $response->assertUnauthorized()
        ->assertJsonPath('error.code', 'USER_LOCKED');
});

it('returns 403 force password change with limited token', function (): void {
    User::factory()->mustChangePassword()->create([
        'email' => 'force@example.com',
        'password_hash' => password_hash('Password123!', PASSWORD_ARGON2ID),
    ]);

    $response = $this->postJson('/api/v1/auth/login', [
        'email' => 'force@example.com',
        'password' => 'Password123!',
    ]);

    $response->assertForbidden()
        ->assertJsonPath('error.code', 'FORCE_PASSWORD_CHANGE')
        ->assertJsonPath('data.token_type', 'bearer')
        ->assertJsonPath('data.expires_in', 300);

    expect($response->json('data.limited_token'))->toBeString();
});

it('returns 401 mfa required when mfa is enabled', function (): void {
    User::factory()->withMfa()->create([
        'email' => 'mfa@example.com',
        'password_hash' => password_hash('Password123!', PASSWORD_ARGON2ID),
    ]);

    $response = $this->postJson('/api/v1/auth/login', [
        'email' => 'mfa@example.com',
        'password' => 'Password123!',
    ]);

    $response->assertUnauthorized()
        ->assertJsonPath('error.code', 'MFA_REQUIRED');
});

it('registers a new user and returns user data with message', function (): void {
    $response = $this->postJson('/api/v1/auth/register', [
        'name' => 'Juan Perez',
        'email' => 'register@example.com',
        'password' => 'Password123!',
        'password_confirmation' => 'Password123!',
        'phone' => '3001234567',
        'unit' => 'Apto 101',
    ]);

    $response->assertCreated()
        ->assertJsonStructure([
            'data' => [
                'id',
                'name',
                'email',
                'phone',
                'unit',
                'role',
                'status',
                'message',
            ],
            'meta' => ['trace_id'],
        ])
        ->assertJsonPath('data.email', 'register@example.com')
        ->assertJsonPath('data.role', 'user')
        ->assertJsonPath('data.message', 'Registro exitoso. Bienvenido a Urbania.');
});

it('returns 409 when email already exists on register', function (): void {
    User::factory()->create(['email' => 'exists@example.com']);

    $response = $this->postJson('/api/v1/auth/register', [
        'name' => 'Otro',
        'email' => 'exists@example.com',
        'password' => 'Password123!',
        'password_confirmation' => 'Password123!',
    ]);

    $response->assertStatus(409)
        ->assertJsonPath('error.code', 'EMAIL_ALREADY_EXISTS');
});

it('returns 422 for invalid register payload', function (): void {
    $response = $this->postJson('/api/v1/auth/register', [
        'name' => '',
        'email' => 'not-an-email',
        'password' => 'short',
        'password_confirmation' => 'different',
    ]);

    $response->assertUnprocessable()
        ->assertJsonPath('error.code', 'VALIDATION_ERROR');
});

it('logs out an authenticated user with refresh token', function (): void {
    $user = User::factory()->create([
        'password_hash' => password_hash('Password123!', PASSWORD_ARGON2ID),
    ]);

    $login = $this->postJson('/api/v1/auth/login', [
        'email' => $user->email,
        'password' => 'Password123!',
    ]);

    $accessToken = $login->json('data.access_token');
    $refreshToken = $login->json('data.refresh_token');

    $response = $this->withHeader('Authorization', 'Bearer '.$accessToken)
        ->postJson('/api/v1/auth/logout', [
            'refresh_token' => $refreshToken,
        ]);

    $response->assertNoContent();
});

it('returns 401 when logging out without token', function (): void {
    $response = $this->postJson('/api/v1/auth/logout', [
        'refresh_token' => 'some-token',
    ]);

    $response->assertUnauthorized()
        ->assertJsonPath('error.code', 'TOKEN_INVALID');
});

it('refreshes tokens with a valid refresh token', function (): void {
    $user = User::factory()->create([
        'password_hash' => password_hash('Password123!', PASSWORD_ARGON2ID),
    ]);

    $login = $this->postJson('/api/v1/auth/login', [
        'email' => $user->email,
        'password' => 'Password123!',
    ]);

    $refreshToken = $login->json('data.refresh_token');

    $response = $this->postJson('/api/v1/auth/refresh', [
        'refresh_token' => $refreshToken,
    ]);

    $response->assertOk()
        ->assertJsonStructure([
            'data' => [
                'access_token',
                'refresh_token',
                'token_type',
                'expires_in',
            ],
            'meta' => ['trace_id'],
        ]);
});

it('returns 401 when refresh token is invalid', function (): void {
    $response = $this->postJson('/api/v1/auth/refresh', [
        'refresh_token' => 'invalid-token',
    ]);

    $response->assertUnauthorized()
        ->assertJsonPath('error.code', 'TOKEN_INVALID');
});

it('returns current user data with valid token', function (): void {
    $user = User::factory()->create();
    $token = generateTokenFor($user);

    $response = $this->withHeader('Authorization', 'Bearer '.$token)
        ->getJson('/api/v1/auth/me');

    $response->assertOk()
        ->assertJsonPath('data.email', $user->email)
        ->assertJsonPath('data.id', $user->id)
        ->assertJsonStructure(['data', 'meta' => ['trace_id']]);
});

it('returns 401 for me without token', function (): void {
    $response = $this->getJson('/api/v1/auth/me');

    $response->assertUnauthorized()
        ->assertJsonPath('error.code', 'TOKEN_INVALID');
});

it('returns 404 for me when user is soft deleted', function (): void {
    $user = User::factory()->softDeleted()->create();
    $token = generateTokenFor($user);

    $response = $this->withHeader('Authorization', 'Bearer '.$token)
        ->getJson('/api/v1/auth/me');

    $response->assertNotFound()
        ->assertJsonPath('error.code', 'USER_NOT_FOUND');
});

it('returns 429 after exceeding login rate limit', function (): void {
    $email = 'rate@example.com';

    for ($i = 0; $i < 5; $i++) {
        $this->postJson('/api/v1/auth/login', [
            'email' => $email,
            'password' => 'WrongPassword!',
        ]);
    }

    $response = $this->postJson('/api/v1/auth/login', [
        'email' => $email,
        'password' => 'WrongPassword!',
    ]);

    $response->assertStatus(429)
        ->assertJsonPath('error.code', 'RATE_LIMIT_EXCEEDED')
        ->assertHeader('Retry-After');
});
