<?php

declare(strict_types=1);

namespace Tests\Feature\Comunicaciones;

use App\Models\Condominium;
use App\Models\MessageTemplate;
use App\Models\Organization;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Str;
use PHPUnit\Framework\Attributes\Test;
use Tests\TestCase;
use Urbania\Auth\Application\Services\JwtServiceInterface;
use Urbania\Auth\Domain\ValueObjects\SessionId;

final class TemplateTest extends TestCase
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
    public function test_can_list_templates(): void
    {
        MessageTemplate::create([
            'condominium_id' => $this->tenantId,
            'nombre' => 'Bienvenida',
            'tipo' => 'email',
            'cuerpo' => 'Bienvenido al condominio.',
        ]);

        $response = $this->withAuth()->getJson('/api/v1/comunicaciones/templates');

        $response->assertOk()
            ->assertJsonStructure([
                'data' => [
                    '*' => ['id', 'condominium_id', 'nombre', 'tipo', 'cuerpo', 'created_at', 'updated_at'],
                ],
                'meta' => ['total', 'current_page', 'per_page', 'last_page', 'trace_id'],
            ])
            ->assertJsonPath('data.0.nombre', 'Bienvenida');
    }

    #[Test]
    public function test_can_create_template(): void
    {
        $response = $this->withAuth()->postJson('/api/v1/comunicaciones/templates', [
            'nombre' => 'Cobro recordatorio',
            'tipo' => 'whatsapp',
            'cuerpo' => 'Recuerde pagar su administración.',
        ]);

        $response->assertCreated()
            ->assertJsonStructure([
                'data' => ['id', 'condominium_id', 'nombre', 'tipo', 'cuerpo', 'created_at', 'updated_at'],
                'meta' => ['trace_id'],
            ])
            ->assertJsonPath('data.nombre', 'Cobro recordatorio')
            ->assertJsonPath('data.tipo', 'whatsapp');
    }

    #[Test]
    public function test_can_update_template(): void
    {
        $template = MessageTemplate::create([
            'condominium_id' => $this->tenantId,
            'nombre' => 'Original',
            'tipo' => 'email',
            'cuerpo' => 'Cuerpo original',
        ]);

        $response = $this->withAuth()->patchJson("/api/v1/comunicaciones/templates/{$template->id}", [
            'nombre' => 'Actualizado',
            'cuerpo' => 'Cuerpo actualizado',
        ]);

        $response->assertOk()
            ->assertJsonPath('data.id', $template->id)
            ->assertJsonPath('data.nombre', 'Actualizado')
            ->assertJsonPath('data.cuerpo', 'Cuerpo actualizado');
    }

    #[Test]
    public function test_can_delete_template(): void
    {
        $template = MessageTemplate::create([
            'condominium_id' => $this->tenantId,
            'nombre' => 'Eliminar',
            'tipo' => 'email',
            'cuerpo' => 'Cuerpo',
        ]);

        $response = $this->withAuth()->deleteJson("/api/v1/comunicaciones/templates/{$template->id}");

        $response->assertNoContent();
        $this->assertSoftDeleted('message_templates', ['id' => $template->id]);
    }

    #[Test]
    public function test_update_returns_404_for_nonexistent(): void
    {
        $response = $this->withAuth()->patchJson('/api/v1/comunicaciones/templates/'.(string) Str::orderedUuid(), [
            'nombre' => 'Actualizado',
            'cuerpo' => 'Cuerpo actualizado',
        ]);

        $response->assertNotFound()
            ->assertJsonPath('error.code', 'TEMPLATE_NOT_FOUND');
    }

    #[Test]
    public function test_delete_returns_404_for_nonexistent(): void
    {
        $response = $this->withAuth()->deleteJson('/api/v1/comunicaciones/templates/'.(string) Str::orderedUuid());

        $response->assertNotFound()
            ->assertJsonPath('error.code', 'TEMPLATE_NOT_FOUND');
    }
}
