<?php

declare(strict_types=1);

namespace Tests\Feature\Comunicaciones;

use App\Models\CommunicationChannel;
use App\Models\Condominium;
use App\Models\Organization;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Str;
use PHPUnit\Framework\Attributes\Test;
use Tests\TestCase;
use Urbania\Auth\Application\Services\JwtServiceInterface;
use Urbania\Auth\Domain\ValueObjects\SessionId;

final class ChannelTest extends TestCase
{
    use RefreshDatabase;

    private string $token;

    private string $tenantId;

    protected function setUp(): void
    {
        parent::setUp();

        $this->tenantId = (string) Str::orderedUuid();

        Organization::factory()->create(['id' => $this->tenantId]);
        Condominium::factory()->create([
            'id' => $this->tenantId,
            'organization_id' => $this->tenantId,
        ]);

        $user = User::factory()->admin()->create([
            'organization_id' => $this->tenantId,
        ]);

        $this->token = app(JwtServiceInterface::class)->generateAccessToken(
            userId: $user->id,
            role: 'admin',
            mfaVerified: false,
            sessionId: SessionId::generate(),
            deviceFingerprint: '',
            organizationId: $this->tenantId,
        )->toString();
    }

    private function withAuth(): self
    {
        return $this->withHeader('Authorization', "Bearer {$this->token}");
    }

    #[Test]
    public function test_can_list_channels(): void
    {
        CommunicationChannel::create([
            'condominium_id' => $this->tenantId,
            'canal' => 'email',
            'provider' => 'smtp',
            'config' => ['host' => 'smtp.example.com'],
            'activo' => true,
        ]);

        $response = $this->withAuth()->getJson('/api/v1/comunicaciones/channels');

        $response->assertOk()
            ->assertJsonStructure([
                'data' => [
                    '*' => ['id', 'condominium_id', 'canal', 'provider', 'activo', 'config_mask'],
                ],
                'meta' => ['trace_id'],
            ])
            ->assertJsonPath('data.0.canal', 'email');
    }

    #[Test]
    public function test_can_update_channel(): void
    {
        $response = $this->withAuth()->putJson('/api/v1/comunicaciones/channels', [
            'canal' => 'whatsapp',
            'provider' => 'twilio',
            'config' => ['sid' => 'AC123'],
            'activo' => true,
        ]);

        $response->assertOk()
            ->assertJsonStructure([
                'data' => ['id', 'condominium_id', 'canal', 'provider', 'activo', 'config_mask'],
                'meta' => ['trace_id'],
            ])
            ->assertJsonPath('data.canal', 'whatsapp')
            ->assertJsonPath('data.provider', 'twilio');

        $this->assertDatabaseHas('communication_channels', [
            'condominium_id' => $this->tenantId,
            'canal' => 'whatsapp',
            'provider' => 'twilio',
        ]);
    }

    #[Test]
    public function test_channel_config_not_exposed(): void
    {
        CommunicationChannel::create([
            'condominium_id' => $this->tenantId,
            'canal' => 'email',
            'provider' => 'smtp',
            'config' => ['api_key' => 'super-secret-key', 'password' => 'super-secret-password'],
            'activo' => true,
        ]);

        $response = $this->withAuth()->getJson('/api/v1/comunicaciones/channels');

        $response->assertOk();

        $payload = json_encode($response->json());
        $this->assertStringNotContainsString('super-secret-key', $payload);
        $this->assertStringNotContainsString('super-secret-password', $payload);
        $response->assertJsonPath('data.0.config_mask', '***');
    }

    #[Test]
    public function test_list_channels_returns_empty_when_none_configured(): void
    {
        $response = $this->withAuth()->getJson('/api/v1/comunicaciones/channels');

        $response->assertOk()
            ->assertJsonStructure([
                'data',
                'meta' => ['trace_id'],
            ])
            ->assertJsonPath('data', []);
    }
}
