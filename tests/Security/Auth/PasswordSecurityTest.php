<?php

declare(strict_types=1);

use App\Models\RefreshToken;
use App\Models\User;
use Illuminate\Foundation\Testing\LazilyRefreshDatabase;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Mail;
use Ramsey\Uuid\Uuid;
use Tests\TestCase;
use Urbania\Auth\Application\Services\JwtServiceInterface;
use Urbania\Auth\Domain\ValueObjects\SessionId;
use Urbania\Auth\Infrastructure\Mail\PasswordResetMail;

function generatePasswordSecurityToken(User $user): string
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

uses(TestCase::class);
uses(LazilyRefreshDatabase::class);

beforeEach(function (): void {
    Mail::fake();
});

it('revokes all refresh tokens after password change', function (): void {
    $user = User::factory()->create([
        'email' => 'revoke@example.com',
        'password_hash' => password_hash('CurrentP@ss123', PASSWORD_ARGON2ID),
    ]);

    RefreshToken::factory()->count(3)->create([
        'user_id' => $user->id,
        'revoked_at' => null,
    ]);

    $token = generatePasswordSecurityToken($user);

    $response = $this->withHeader('Authorization', 'Bearer '.$token)
        ->postJson('/api/v1/auth/change-password', [
            'current_password' => 'CurrentP@ss123',
            'new_password' => 'NewSecureP@ss123',
            'new_password_confirmation' => 'NewSecureP@ss123',
        ]);

    $response->assertOk();

    $activeTokens = RefreshToken::where('user_id', $user->id)
        ->whereNull('revoked_at')
        ->count();

    expect($activeTokens)->toBe(0);
});

it('revokes all refresh tokens after password reset', function (): void {
    $user = User::factory()->create([
        'email' => 'reset-revoke@example.com',
        'password_hash' => password_hash('OldP@ss123', PASSWORD_ARGON2ID),
    ]);

    RefreshToken::factory()->count(2)->create([
        'user_id' => $user->id,
        'revoked_at' => null,
    ]);

    $this->postJson('/api/v1/auth/forgot-password', [
        'email' => 'reset-revoke@example.com',
    ]);

    $resetToken = null;
    Mail::assertSent(PasswordResetMail::class, function (PasswordResetMail $mail) use (&$resetToken): bool {
        $matches = [];
        preg_match('/token=([a-f0-9]{128})/', $mail->resetLink, $matches);
        $resetToken = $matches[1] ?? null;

        return true;
    });

    $response = $this->postJson('/api/v1/auth/reset-password', [
        'email' => 'reset-revoke@example.com',
        'token' => $resetToken,
        'password' => 'NewSecureP@ss123',
        'password_confirmation' => 'NewSecureP@ss123',
    ]);

    $response->assertOk();

    $activeTokens = RefreshToken::where('user_id', $user->id)
        ->whereNull('revoked_at')
        ->count();

    expect($activeTokens)->toBe(0);
});

it('rejects password reuse from history', function (): void {
    $user = User::factory()->create([
        'email' => 'history@example.com',
        'password_hash' => password_hash('CurrentP@ss123', PASSWORD_ARGON2ID),
    ]);

    $oldHash = password_hash('OldP@ss123', PASSWORD_ARGON2ID);
    DB::table('password_history')->insert([
        'id' => (string) Uuid::uuid7(),
        'user_id' => $user->id,
        'password_hash' => $oldHash,
        'created_at' => now(),
    ]);

    $token = generatePasswordSecurityToken($user);

    $response = $this->withHeader('Authorization', 'Bearer '.$token)
        ->postJson('/api/v1/auth/change-password', [
            'current_password' => 'CurrentP@ss123',
            'new_password' => 'OldP@ss123',
            'new_password_confirmation' => 'OldP@ss123',
        ]);

    $response->assertStatus(400)
        ->assertJsonPath('error.code', 'PASSWORD_REUSED');
});

it('limits password history to 12 records via database trigger', function (): void {
    $user = User::factory()->create();

    for ($i = 0; $i < 15; $i++) {
        DB::table('password_history')->insert([
            'id' => (string) Uuid::uuid7(),
            'user_id' => $user->id,
            'password_hash' => password_hash("Password{$i}!", PASSWORD_ARGON2ID),
            'created_at' => now()->subSeconds($i),
        ]);
    }

    $count = DB::table('password_history')->where('user_id', $user->id)->count();

    expect($count)->toBeLessThanOrEqual(12);
});

it('does not reveal email existence on forgot password', function (): void {
    $existingResponse = $this->postJson('/api/v1/auth/forgot-password', [
        'email' => 'exists@example.com',
    ]);

    $missingResponse = $this->postJson('/api/v1/auth/forgot-password', [
        'email' => 'missing@example.com',
    ]);

    expect($existingResponse->status())->toBe($missingResponse->status())
        ->and($existingResponse->json('data.message'))->toBe($missingResponse->json('data.message'));
});

it('does not reveal email existence on reset password', function (): void {
    User::factory()->create([
        'email' => 'reset-exists@example.com',
    ]);

    $existingResponse = $this->postJson('/api/v1/auth/reset-password', [
        'email' => 'reset-exists@example.com',
        'token' => 'invalid-token',
        'password' => 'NewSecureP@ss123',
        'password_confirmation' => 'NewSecureP@ss123',
    ]);

    $missingResponse = $this->postJson('/api/v1/auth/reset-password', [
        'email' => 'reset-missing@example.com',
        'token' => 'invalid-token',
        'password' => 'NewSecureP@ss123',
        'password_confirmation' => 'NewSecureP@ss123',
    ]);

    expect($existingResponse->status())->toBe($missingResponse->status())
        ->and($existingResponse->json('error.code'))->toBe($missingResponse->json('error.code'));
});
