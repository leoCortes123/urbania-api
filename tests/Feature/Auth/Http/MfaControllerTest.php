<?php

declare(strict_types=1);

use App\Models\User;
use Illuminate\Foundation\Testing\LazilyRefreshDatabase;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Redis;
use PragmaRX\Google2FA\Google2FA;
use Urbania\Auth\Application\Services\JwtServiceInterface;
use Urbania\Auth\Domain\ValueObjects\SessionId;

uses(LazilyRefreshDatabase::class);

function generateAccessTokenFor(User $user): string
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
});

it('sets up MFA and returns secret qr code and backup codes', function (): void {
    $user = User::factory()->create([
        'password_hash' => password_hash('Password123!', PASSWORD_ARGON2ID),
    ]);
    $token = generateAccessTokenFor($user);

    $response = $this->withHeader('Authorization', 'Bearer '.$token)
        ->postJson('/api/v1/auth/mfa/setup');

    $response->assertOk()
        ->assertJsonStructure([
            'data' => [
                'secret',
                'qr_code_url',
                'backup_codes',
            ],
            'meta' => ['trace_id'],
        ])
        ->assertJsonCount(10, 'data.backup_codes')
        ->assertJsonPath('data.backup_codes.0', fn (string $code): bool => preg_match('/^\d{8}$/', $code) === 1);
});

it('verifies MFA and returns tokens', function (): void {
    $google2fa = new Google2FA;
    $google2fa->setAlgorithm('sha256');
    $secret = $google2fa->generateSecretKey(32);
    $code = $google2fa->getCurrentOtp($secret);

    $user = User::factory()->withMfa()->create([
        'mfa_secret' => $secret,
        'mfa_backup_codes' => [],
        'password_hash' => password_hash('Password123!', PASSWORD_ARGON2ID),
    ]);

    $login = $this->postJson('/api/v1/auth/login', [
        'email' => $user->email,
        'password' => 'Password123!',
    ]);

    $mfaToken = $login->json('data.limited_token');

    $response = $this->postJson('/api/v1/auth/mfa/verify', [
        'mfa_token' => $mfaToken,
        'code' => $code,
    ]);

    $response->assertOk()
        ->assertJsonStructure([
            'data' => [
                'access_token',
                'refresh_token',
                'token_type',
                'expires_in',
                'user',
            ],
            'meta' => ['trace_id'],
        ]);
});

it('returns 401 for invalid MFA code', function (): void {
    $google2fa = new Google2FA;
    $google2fa->setAlgorithm('sha256');
    $secret = $google2fa->generateSecretKey(32);

    $user = User::factory()->withMfa()->create([
        'mfa_secret' => $secret,
        'password_hash' => password_hash('Password123!', PASSWORD_ARGON2ID),
    ]);

    $login = $this->postJson('/api/v1/auth/login', [
        'email' => $user->email,
        'password' => 'Password123!',
    ]);

    $mfaToken = $login->json('data.limited_token');

    $response = $this->postJson('/api/v1/auth/mfa/verify', [
        'mfa_token' => $mfaToken,
        'code' => '000000',
    ]);

    $response->assertUnauthorized()
        ->assertJsonPath('error.code', 'MFA_INVALID_CODE');
});

it('enables MFA with valid code', function (): void {
    $google2fa = new Google2FA;
    $google2fa->setAlgorithm('sha256');
    $secret = $google2fa->generateSecretKey(32);
    $code = $google2fa->getCurrentOtp($secret);

    $user = User::factory()->create([
        'mfa_secret' => $secret,
        'mfa_backup_codes' => array_map(
            fn (string $code): string => password_hash($code, PASSWORD_ARGON2ID),
            ['11111111', '22222222'],
        ),
        'password_hash' => password_hash('Password123!', PASSWORD_ARGON2ID),
    ]);
    $token = generateAccessTokenFor($user);

    $response = $this->withHeader('Authorization', 'Bearer '.$token)
        ->postJson('/api/v1/auth/mfa/enable', [
            'code' => $code,
        ]);

    $response->assertOk()
        ->assertJsonPath('data.message', 'MFA habilitado exitosamente');

    $user->refresh();
    expect($user->mfa_enabled)->toBeTrue();
});

it('disables MFA with valid password and code', function (): void {
    $google2fa = new Google2FA;
    $google2fa->setAlgorithm('sha256');
    $secret = $google2fa->generateSecretKey(32);
    $code = $google2fa->getCurrentOtp($secret);

    $user = User::factory()->withMfa()->create([
        'mfa_secret' => $secret,
        'password_hash' => password_hash('Password123!', PASSWORD_ARGON2ID),
    ]);
    $token = generateAccessTokenFor($user);

    $response = $this->withHeader('Authorization', 'Bearer '.$token)
        ->postJson('/api/v1/auth/mfa/disable', [
            'password' => 'Password123!',
            'code' => $code,
        ]);

    $response->assertOk()
        ->assertJsonPath('data.message', 'MFA deshabilitado exitosamente');

    $user->refresh();
    expect($user->mfa_enabled)->toBeFalse();
});

it('regenerates backup codes for enabled MFA', function (): void {
    $user = User::factory()->withMfa()->create([
        'password_hash' => password_hash('Password123!', PASSWORD_ARGON2ID),
    ]);
    $token = generateAccessTokenFor($user);

    $response = $this->withHeader('Authorization', 'Bearer '.$token)
        ->postJson('/api/v1/auth/mfa/backup-codes');

    $response->assertOk()
        ->assertJsonCount(10, 'data.backup_codes')
        ->assertJsonPath('data.backup_codes.0', fn (string $code): bool => preg_match('/^\d{8}$/', $code) === 1);
});
