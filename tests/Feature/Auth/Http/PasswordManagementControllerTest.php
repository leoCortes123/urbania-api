<?php

declare(strict_types=1);

use App\Models\User;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Mail;
use Urbania\Auth\Application\Services\JwtServiceInterface;
use Urbania\Auth\Domain\ValueObjects\SessionId;
use Urbania\Auth\Infrastructure\Mail\EmailVerificationMail;
use Urbania\Auth\Infrastructure\Mail\PasswordResetMail;

function generateAccessTokenForPassword(User $user): string
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

function generateVerificationTokenForPassword(User $user): string
{
    $service = app(JwtServiceInterface::class);

    return $service->generateAccessToken(
        userId: $user->id,
        role: $user->role,
        mfaVerified: false,
        sessionId: SessionId::generate(),
        deviceFingerprint: '',
        organizationId: $user->organization_id,
        scope: 'email-verification',
        ttl: 3600,
    )->toString();
}

beforeEach(function (): void {
    Mail::fake();
});

it('sends password reset email for existing user', function (): void {
    $user = User::factory()->create([
        'email' => 'forgot@example.com',
    ]);

    $response = $this->postJson('/api/v1/auth/forgot-password', [
        'email' => 'forgot@example.com',
    ]);

    $response->assertOk()
        ->assertJsonPath('data.message', 'Si el correo existe, recibirás un enlace para restablecer tu contraseña.');

    Mail::assertSent(PasswordResetMail::class, function (PasswordResetMail $mail) use ($user): bool {
        return $mail->hasTo($user->email);
    });

    expect(DB::table('password_reset_tokens')->where('email', 'forgot@example.com')->exists())->toBeTrue();
});

it('returns success for forgot password even when email does not exist', function (): void {
    $response = $this->postJson('/api/v1/auth/forgot-password', [
        'email' => 'missing@example.com',
    ]);

    $response->assertOk()
        ->assertJsonPath('data.message', 'Si el correo existe, recibirás un enlace para restablecer tu contraseña.');

    Mail::assertNothingSent();
});

it('returns 429 after exceeding forgot password rate limit', function (): void {
    $email = 'rate-forgot@example.com';

    for ($i = 0; $i < 3; $i++) {
        $this->postJson('/api/v1/auth/forgot-password', [
            'email' => $email,
        ]);
    }

    $response = $this->postJson('/api/v1/auth/forgot-password', [
        'email' => $email,
    ]);

    $response->assertStatus(429)
        ->assertJsonPath('error.code', 'RATE_LIMIT_EXCEEDED');
});

it('resets password with valid token', function (): void {
    $user = User::factory()->create([
        'email' => 'reset@example.com',
        'password_hash' => password_hash('OldP@ss123', PASSWORD_ARGON2ID),
    ]);

    $this->postJson('/api/v1/auth/forgot-password', [
        'email' => 'reset@example.com',
    ]);

    $token = null;
    Mail::assertSent(PasswordResetMail::class, function (PasswordResetMail $mail) use (&$token): bool {
        $matches = [];
        preg_match('/token=([a-f0-9]{128})/', $mail->resetLink, $matches);
        $token = $matches[1] ?? null;

        return true;
    });

    $response = $this->postJson('/api/v1/auth/reset-password', [
        'email' => 'reset@example.com',
        'token' => $token,
        'password' => 'NewSecureP@ss123',
        'password_confirmation' => 'NewSecureP@ss123',
    ]);

    $response->assertOk()
        ->assertJsonPath('data.message', 'Contraseña restablecida exitosamente.');

    $user->refresh();
    expect(password_verify('NewSecureP@ss123', $user->password_hash))->toBeTrue();
    expect(DB::table('password_reset_tokens')->where('email', 'reset@example.com')->exists())->toBeFalse();
});

it('returns 400 for invalid reset token', function (): void {
    User::factory()->create([
        'email' => 'reset-invalid@example.com',
    ]);

    $response = $this->postJson('/api/v1/auth/reset-password', [
        'email' => 'reset-invalid@example.com',
        'token' => 'invalid-token',
        'password' => 'NewSecureP@ss123',
        'password_confirmation' => 'NewSecureP@ss123',
    ]);

    $response->assertStatus(400)
        ->assertJsonPath('error.code', 'INVALID_RESET_TOKEN');
});

it('changes password for authenticated user', function (): void {
    $user = User::factory()->create([
        'email' => 'change@example.com',
        'password_hash' => password_hash('CurrentP@ss123', PASSWORD_ARGON2ID),
    ]);

    $token = generateAccessTokenForPassword($user);

    $response = $this->withHeader('Authorization', 'Bearer '.$token)
        ->postJson('/api/v1/auth/change-password', [
            'current_password' => 'CurrentP@ss123',
            'new_password' => 'NewSecureP@ss123',
            'new_password_confirmation' => 'NewSecureP@ss123',
        ]);

    $response->assertOk()
        ->assertJsonPath('data.message', 'Contraseña actualizada exitosamente.');

    $user->refresh();
    expect(password_verify('NewSecureP@ss123', $user->password_hash))->toBeTrue();
});

it('returns 401 when current password is wrong', function (): void {
    $user = User::factory()->create([
        'email' => 'change-wrong@example.com',
        'password_hash' => password_hash('CurrentP@ss123', PASSWORD_ARGON2ID),
    ]);

    $token = generateAccessTokenForPassword($user);

    $response = $this->withHeader('Authorization', 'Bearer '.$token)
        ->postJson('/api/v1/auth/change-password', [
            'current_password' => 'WrongP@ss123',
            'new_password' => 'NewSecureP@ss123',
            'new_password_confirmation' => 'NewSecureP@ss123',
        ]);

    $response->assertUnauthorized()
        ->assertJsonPath('error.code', 'INVALID_CREDENTIALS');
});

it('updates profile for authenticated user', function (): void {
    $user = User::factory()->create([
        'email' => 'profile@example.com',
    ]);

    $token = generateAccessTokenForPassword($user);

    $response = $this->withHeader('Authorization', 'Bearer '.$token)
        ->patchJson('/api/v1/auth/me', [
            'name' => 'Updated Name',
            'phone' => '3007654321',
        ]);

    $response->assertOk()
        ->assertJsonPath('data.name', 'Updated Name')
        ->assertJsonPath('data.phone', '3007654321');

    $user->refresh();
    expect($user->name)->toBe('Updated Name')
        ->and($user->phone)->toBe('3007654321');
});

it('returns 401 for update profile without token', function (): void {
    $response = $this->patchJson('/api/v1/auth/me', [
        'name' => 'Updated Name',
    ]);

    $response->assertUnauthorized()
        ->assertJsonPath('error.code', 'TENANT_REQUIRED');
});

it('verifies email with valid token', function (): void {
    $user = User::factory()->create([
        'email' => 'verify@example.com',
        'email_verified_at' => null,
    ]);

    $token = generateVerificationTokenForPassword($user);

    $response = $this->postJson('/api/v1/auth/verify-email', [
        'token' => $token,
    ]);

    $response->assertOk()
        ->assertJsonPath('data.message', 'Correo electrónico verificado exitosamente.');

    $user->refresh();
    expect($user->email_verified_at)->not->toBeNull();
});

it('returns 409 when email is already verified', function (): void {
    $user = User::factory()->create([
        'email' => 'verified@example.com',
        'email_verified_at' => now(),
    ]);

    $token = generateVerificationTokenForPassword($user);

    $response = $this->postJson('/api/v1/auth/verify-email', [
        'token' => $token,
    ]);

    $response->assertStatus(409)
        ->assertJsonPath('error.code', 'EMAIL_ALREADY_VERIFIED');
});

it('returns 400 for invalid verification token', function (): void {
    $response = $this->postJson('/api/v1/auth/verify-email', [
        'token' => 'invalid-token',
    ]);

    $response->assertStatus(400)
        ->assertJsonPath('error.code', 'EMAIL_VERIFICATION_INVALID');
});

it('resends verification email for authenticated unverified user', function (): void {
    $user = User::factory()->create([
        'email' => 'resend@example.com',
        'email_verified_at' => null,
    ]);

    $token = generateAccessTokenForPassword($user);

    $response = $this->withHeader('Authorization', 'Bearer '.$token)
        ->postJson('/api/v1/auth/resend-verification');

    $response->assertOk()
        ->assertJsonPath('data.message', 'Se ha enviado un nuevo enlace de verificación.');

    Mail::assertSent(EmailVerificationMail::class, function (EmailVerificationMail $mail) use ($user): bool {
        return $mail->hasTo($user->email);
    });
});

it('returns 409 when resending verification for already verified user', function (): void {
    $user = User::factory()->create([
        'email' => 'resend-verified@example.com',
        'email_verified_at' => now(),
    ]);

    $token = generateAccessTokenForPassword($user);

    $response = $this->withHeader('Authorization', 'Bearer '.$token)
        ->postJson('/api/v1/auth/resend-verification');

    $response->assertStatus(409)
        ->assertJsonPath('error.code', 'EMAIL_ALREADY_VERIFIED');
});

it('returns 429 after exceeding resend verification rate limit', function (): void {
    $user = User::factory()->create([
        'email' => 'rate-resend@example.com',
        'email_verified_at' => null,
    ]);

    $token = generateAccessTokenForPassword($user);

    for ($i = 0; $i < 3; $i++) {
        $this->withHeader('Authorization', 'Bearer '.$token)
            ->postJson('/api/v1/auth/resend-verification');
    }

    $response = $this->withHeader('Authorization', 'Bearer '.$token)
        ->postJson('/api/v1/auth/resend-verification');

    $response->assertStatus(429)
        ->assertJsonPath('error.code', 'RATE_LIMIT_EXCEEDED');
});
