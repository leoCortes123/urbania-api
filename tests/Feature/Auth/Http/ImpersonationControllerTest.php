<?php

declare(strict_types=1);

namespace Tests\Feature\Auth\Http;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

final class ImpersonationControllerTest extends TestCase
{
    use RefreshDatabase;

    private User $admin;
    private User $targetUser;
    private string $adminToken;

    protected function setUp(): void
    {
        parent::setUp();

        $this->admin = User::factory()->create([
            'role' => 'admin',
            'status' => 'active',
        ]);

        $this->targetUser = User::factory()->create([
            'role' => 'user',
            'status' => 'active',
        ]);

        $this->adminToken = $this->generateTokenFor($this->admin);
    }

    public function testImpersonateStartsSuccessfully(): void
    {
        $response = $this->withHeader('Authorization', 'Bearer '.$this->adminToken)
            ->postJson('/api/v1/admin/impersonate', [
                'user_id' => $this->targetUser->id,
                'reason' => 'Soporte técnico',
            ]);

        $response->assertStatus(201);
        $response->assertJsonStructure([
            'data' => [
                'token',
                'expires_in',
                'session_id',
            ],
            'meta' => [
                'trace_id',
            ],
        ]);

        $data = $response->json('data');
        $this->assertNotEmpty($data['token']);
        $this->assertSame(900, $data['expires_in']);
        $this->assertNotEmpty($data['session_id']);
    }

    public function testImpersonateReturns401WithoutToken(): void
    {
        $response = $this->postJson('/api/v1/admin/impersonate', [
            'user_id' => $this->targetUser->id,
            'reason' => 'Test',
        ]);

        $response->assertStatus(401);
    }

    public function testImpersonateReturns422ForMissingFields(): void
    {
        $response = $this->withHeader('Authorization', 'Bearer '.$this->adminToken)
            ->postJson('/api/v1/admin/impersonate', []);

        $response->assertStatus(422);
    }

    public function testImpersonateReturns404ForNonexistentUser(): void
    {
        $response = $this->withHeader('Authorization', 'Bearer '.$this->adminToken)
            ->postJson('/api/v1/admin/impersonate', [
                'user_id' => '018f4c80-0000-7000-0000-000000000000',
                'reason' => 'Test',
            ]);

        $response->assertStatus(404);
        $this->assertSame('USER_NOT_FOUND', $response->json('error.code'));
    }

    public function testImpersonateStopRequiresImpersonationToken(): void
    {
        // Try to stop impersonation with a regular admin token (no imp claim)
        $response = $this->withHeader('Authorization', 'Bearer '.$this->adminToken)
            ->postJson('/api/v1/admin/impersonate/stop');

        $response->assertStatus(403);
    }

    public function testImpersonateStopReturns204WithValidImpersonationToken(): void
    {
        // First, start an impersonation
        $impersonateResponse = $this->withHeader('Authorization', 'Bearer '.$this->adminToken)
            ->postJson('/api/v1/admin/impersonate', [
                'user_id' => $this->targetUser->id,
                'reason' => 'Test stop',
            ]);

        $impersonationToken = $impersonateResponse->json('data.token');

        // Then stop it
        $stopResponse = $this->withHeader('Authorization', 'Bearer '.$impersonationToken)
            ->postJson('/api/v1/admin/impersonate/stop');

        $stopResponse->assertStatus(204);
    }

    public function testFullImpersonationFlow(): void
    {
        // 1. Start impersonation
        $startResponse = $this->withHeader('Authorization', 'Bearer '.$this->adminToken)
            ->postJson('/api/v1/admin/impersonate', [
                'user_id' => $this->targetUser->id,
                'reason' => 'Testing full flow',
            ]);

        $startResponse->assertStatus(201);
        $token = $startResponse->json('data.token');
        $sessionId = $startResponse->json('data.session_id');

        // 2. Use impersonation token to access a protected endpoint (me)
        $meResponse = $this->withHeader('Authorization', 'Bearer '.$token)
            ->getJson('/api/v1/auth/me');

        $meResponse->assertStatus(200);
        // Should return the impersonated user's data
        $this->assertSame($this->targetUser->id, $meResponse->json('data.id'));

        // 3. Stop impersonation
        $stopResponse = $this->withHeader('Authorization', 'Bearer '.$token)
            ->postJson('/api/v1/admin/impersonate/stop');

        $stopResponse->assertStatus(204);
    }

    /**
     * Generate a valid JWT token for the given user.
     */
    private function generateTokenFor(User $user): string
    {
        $response = $this->postJson('/api/v1/auth/login', [
            'email' => $user->email,
            'password' => 'Password123!', // UserFactory default
        ]);

        $data = $response->json('data');

        return $data['access_token'] ?? '';
    }
}
