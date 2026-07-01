<?php

declare(strict_types=1);

namespace Tests\Feature\Comunicaciones;

use App\Models\Announcement;
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

final class AnnouncementTest extends TestCase
{
    use RefreshDatabase;

    private string $token;

    private string $tenantId;

    private User $user;

    protected function setUp(): void
    {
        parent::setUp();

        $this->tenantId = (string) Str::orderedUuid();

        Organization::factory()->create(['id' => $this->tenantId]);
        Condominium::factory()->create([
            'id' => $this->tenantId,
            'organization_id' => $this->tenantId,
        ]);

        CommunicationChannel::create([
            'condominium_id' => $this->tenantId,
            'canal' => 'email',
            'provider' => 'smtp',
            'config' => ['api_key' => 'secret'],
            'activo' => true,
        ]);

        $this->user = User::factory()->admin()->create([
            'organization_id' => $this->tenantId,
        ]);

        $this->token = app(JwtServiceInterface::class)->generateAccessToken(
            userId: $this->user->id,
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
    public function test_can_list_announcements(): void
    {
        Announcement::create([
            'condominium_id' => $this->tenantId,
            'autor_user_id' => $this->user->id,
            'titulo' => 'Aviso importante',
            'cuerpo' => 'Contenido del aviso',
            'segmento' => 'todos',
            'target_id' => null,
            'estado' => 'borrador',
            'programado_para' => null,
            'fijado' => false,
            'canales' => ['email'],
        ]);

        $response = $this->withAuth()->getJson('/api/v1/comunicaciones/announcements');

        $response->assertOk()
            ->assertJsonStructure([
                'data' => [
                    'items',
                    'meta' => ['total', 'current_page', 'per_page', 'last_page'],
                ],
                'meta' => ['trace_id'],
            ])
            ->assertJsonPath('data.meta.total', 1);
    }

    #[Test]
    public function test_can_create_announcement(): void
    {
        $response = $this->withAuth()->postJson('/api/v1/comunicaciones/announcements', [
            'titulo' => 'Asamblea general',
            'cuerpo' => 'Se convoca a todos los residentes.',
            'segmento' => 'todos',
            'canales' => ['email'],
            'fijado' => true,
        ]);

        $response->assertCreated()
            ->assertJsonStructure([
                'data' => ['id', 'condominium_id', 'autor_user_id', 'titulo', 'cuerpo', 'segmento', 'estado', 'canales', 'created_at', 'updated_at'],
                'meta' => ['trace_id'],
            ])
            ->assertJsonPath('data.titulo', 'Asamblea general')
            ->assertJsonPath('data.segmento', 'todos')
            ->assertJsonPath('data.estado', 'borrador');
    }

    #[Test]
    public function test_create_announcement_validates_required_fields(): void
    {
        $response = $this->withAuth()->postJson('/api/v1/comunicaciones/announcements', []);

        $response->assertUnprocessable()
            ->assertJsonPath('error.code', 'VALIDATION_ERROR')
            ->assertJsonStructure(['error' => ['code', 'message', 'trace_id']]);
    }

    #[Test]
    public function test_create_announcement_requires_target_for_torre(): void
    {
        $response = $this->withAuth()->postJson('/api/v1/comunicaciones/announcements', [
            'titulo' => 'Aviso torre',
            'cuerpo' => 'Contenido',
            'segmento' => 'torre',
            'canales' => ['email'],
        ]);

        $response->assertUnprocessable()
            ->assertJsonPath('error.code', 'VALIDATION_ERROR');
    }

    #[Test]
    public function test_can_show_announcement_with_metrics(): void
    {
        $announcement = Announcement::create([
            'condominium_id' => $this->tenantId,
            'autor_user_id' => $this->user->id,
            'titulo' => 'Aviso',
            'cuerpo' => 'Cuerpo',
            'segmento' => 'todos',
            'target_id' => null,
            'estado' => 'borrador',
            'programado_para' => null,
            'fijado' => false,
            'canales' => ['email'],
        ]);

        $response = $this->withAuth()->getJson("/api/v1/comunicaciones/announcements/{$announcement->id}");

        $response->assertOk()
            ->assertJsonPath('data.id', $announcement->id)
            ->assertJsonPath('data.titulo', 'Aviso')
            ->assertJsonStructure([
                'data' => [
                    'id',
                    'titulo',
                    'breakdown' => ['byStatus', 'byChannel'],
                ],
                'meta' => ['trace_id'],
            ]);
    }

    #[Test]
    public function test_show_returns_404_for_nonexistent(): void
    {
        $response = $this->withAuth()->getJson('/api/v1/comunicaciones/announcements/'.(string) Str::orderedUuid());

        $response->assertNotFound()
            ->assertJsonPath('error.code', 'ANNOUNCEMENT_NOT_FOUND');
    }

    #[Test]
    public function test_can_delete_announcement(): void
    {
        $announcement = Announcement::create([
            'condominium_id' => $this->tenantId,
            'autor_user_id' => $this->user->id,
            'titulo' => 'Aviso a eliminar',
            'cuerpo' => 'Contenido',
            'segmento' => 'todos',
            'target_id' => null,
            'estado' => 'borrador',
            'programado_para' => null,
            'fijado' => false,
            'canales' => ['email'],
        ]);

        $response = $this->withAuth()->deleteJson("/api/v1/comunicaciones/announcements/{$announcement->id}");

        $response->assertNoContent();
        $this->assertSoftDeleted('announcements', ['id' => $announcement->id]);
    }

    #[Test]
    public function test_delete_returns_404_for_nonexistent(): void
    {
        $response = $this->withAuth()->deleteJson('/api/v1/comunicaciones/announcements/'.(string) Str::orderedUuid());

        $response->assertNotFound()
            ->assertJsonPath('error.code', 'ANNOUNCEMENT_NOT_FOUND');
    }
}
