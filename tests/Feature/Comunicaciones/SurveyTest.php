<?php

declare(strict_types=1);

namespace Tests\Feature\Comunicaciones;

use App\Models\Condominium;
use App\Models\Contact;
use App\Models\Organization;
use App\Models\SurveyOption;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Str;
use PHPUnit\Framework\Attributes\Test;
use Tests\TestCase;
use Urbania\Auth\Application\Services\JwtServiceInterface;
use Urbania\Auth\Domain\ValueObjects\SessionId;

final class SurveyTest extends TestCase
{
    use RefreshDatabase;

    private string $adminToken;

    private string $tenantId;

    private Contact $contact;

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

        $this->contact = Contact::create([
            'user_id' => $user->id,
            'document_type' => 'CC',
            'document_number' => '12345678',
            'full_name' => 'Residente Prueba',
            'email' => 'residente@example.com',
            'organization_id' => $this->tenantId,
        ]);

        $this->adminToken = app(JwtServiceInterface::class)->generateAccessToken(
            userId: $user->id,
            role: 'admin',
            mfaVerified: false,
            sessionId: SessionId::generate(),
            deviceFingerprint: '',
            organizationId: $this->tenantId,
        )->toString();
    }

    private function withAuth(string $token): self
    {
        return $this->withHeader('Authorization', "Bearer {$token}");
    }

    #[Test]
    public function test_can_list_surveys(): void
    {
        $surveyAResponse = $this->withAuth($this->adminToken)->postJson('/api/v1/comunicaciones/surveys', [
            'pregunta' => 'Encuesta A',
            'tipo' => 'simple',
            'opciones' => ['Sí', 'No'],
        ]);
        $surveyAId = $surveyAResponse->json('data.id');

        $surveyBResponse = $this->withAuth($this->adminToken)->postJson('/api/v1/comunicaciones/surveys', [
            'pregunta' => 'Encuesta B',
            'tipo' => 'simple',
            'opciones' => ['A', 'B', 'C'],
        ]);
        $surveyBId = $surveyBResponse->json('data.id');

        $response = $this->withAuth($this->adminToken)->getJson('/api/v1/comunicaciones/surveys');

        $response->assertOk()
            ->assertJsonStructure([
                'data' => [
                    'items' => [
                        '*' => ['id', 'pregunta', 'tipo', 'cierra_el', 'activa', 'opciones_count', 'responses_count', 'created_at'],
                    ],
                    'total',
                    'page',
                    'perPage',
                    'lastPage',
                ],
                'meta' => ['trace_id'],
            ])
            ->assertJsonPath('data.total', 2)
            ->assertJsonPath('data.page', 1)
            ->assertJsonPath('data.perPage', 20);

        $items = $response->json('data.items');
        $ids = array_map(fn (array $item) => $item['id'], $items);
        $this->assertContains($surveyAId, $ids);
        $this->assertContains($surveyBId, $ids);

        $surveyA = collect($items)->first(fn (array $item) => $item['id'] === $surveyAId);
        $surveyB = collect($items)->first(fn (array $item) => $item['id'] === $surveyBId);
        $this->assertNotNull($surveyA);
        $this->assertNotNull($surveyB);
        $this->assertSame(2, $surveyA['opciones_count']);
        $this->assertSame(3, $surveyB['opciones_count']);
        $this->assertSame(0, $surveyA['responses_count']);
        $this->assertSame(0, $surveyB['responses_count']);

        $filtered = $this->withAuth($this->adminToken)->getJson('/api/v1/comunicaciones/surveys?activa=1');
        $filtered->assertOk()->assertJsonPath('data.total', 2);

        $pageTwo = $this->withAuth($this->adminToken)->getJson('/api/v1/comunicaciones/surveys?page=2');
        $pageTwo->assertOk()->assertJsonPath('data.page', 2);
    }

    #[Test]
    public function test_can_create_survey_with_options(): void
    {
        $response = $this->withAuth($this->adminToken)->postJson('/api/v1/comunicaciones/surveys', [
            'pregunta' => '¿Aprueba el presupuesto?',
            'tipo' => 'simple',
            'opciones' => ['Sí', 'No'],
        ]);

        $response->assertCreated()
            ->assertJsonStructure([
                'data' => ['id', 'condominium_id', 'pregunta', 'tipo', 'activa', 'opciones', 'created_at', 'updated_at'],
                'meta' => ['trace_id'],
            ])
            ->assertJsonPath('data.pregunta', '¿Aprueba el presupuesto?')
            ->assertJsonPath('data.opciones', ['Sí', 'No']);

        $surveyId = $response->json('data.id');
        $this->assertDatabaseHas('surveys', ['id' => $surveyId]);
        $this->assertDatabaseCount('survey_options', 2);
    }

    #[Test]
    public function test_can_respond_to_survey(): void
    {
        $surveyResponse = $this->withAuth($this->adminToken)->postJson('/api/v1/comunicaciones/surveys', [
            'pregunta' => 'Pregunta',
            'tipo' => 'simple',
            'opciones' => ['A', 'B'],
        ]);

        $surveyId = $surveyResponse->json('data.id');
        $optionId = SurveyOption::where('survey_id', $surveyId)->firstOrFail()->id;

        $response = $this->withAuth($this->adminToken)->postJson("/api/v1/comunicaciones/surveys/{$surveyId}/responses", [
            'option_id' => $optionId,
        ]);

        $response->assertCreated()
            ->assertJsonStructure([
                'data' => ['id', 'survey_id', 'contact_id', 'option_id', 'created_at', 'updated_at'],
                'meta' => ['trace_id'],
            ])
            ->assertJsonPath('data.survey_id', $surveyId)
            ->assertJsonPath('data.contact_id', $this->contact->id);
    }

    #[Test]
    public function test_cannot_respond_twice(): void
    {
        $surveyResponse = $this->withAuth($this->adminToken)->postJson('/api/v1/comunicaciones/surveys', [
            'pregunta' => 'Pregunta',
            'tipo' => 'simple',
            'opciones' => ['A', 'B'],
        ]);

        $surveyId = $surveyResponse->json('data.id');
        $optionId = SurveyOption::where('survey_id', $surveyId)->firstOrFail()->id;

        $this->withAuth($this->adminToken)->postJson("/api/v1/comunicaciones/surveys/{$surveyId}/responses", [
            'option_id' => $optionId,
        ])->assertCreated();

        $second = $this->withAuth($this->adminToken)->postJson("/api/v1/comunicaciones/surveys/{$surveyId}/responses", [
            'option_id' => $optionId,
        ]);

        $second->assertStatus(409)
            ->assertJsonPath('error.code', 'ALREADY_ANSWERED');
    }

    #[Test]
    public function test_can_get_survey_results(): void
    {
        $surveyResponse = $this->withAuth($this->adminToken)->postJson('/api/v1/comunicaciones/surveys', [
            'pregunta' => 'Pregunta',
            'tipo' => 'simple',
            'opciones' => ['A', 'B'],
        ]);

        $surveyId = $surveyResponse->json('data.id');
        $optionId = SurveyOption::where('survey_id', $surveyId)->firstOrFail()->id;

        $this->withAuth($this->adminToken)->postJson("/api/v1/comunicaciones/surveys/{$surveyId}/responses", [
            'option_id' => $optionId,
        ])->assertCreated();

        $response = $this->withAuth($this->adminToken)->getJson("/api/v1/comunicaciones/surveys/{$surveyId}/results");

        $response->assertOk()
            ->assertJsonStructure([
                'data' => [
                    'survey_id',
                    'total',
                    'conteos' => [
                        '*' => ['option_id', 'texto', 'count', 'cerrada'],
                    ],
                ],
                'meta' => ['trace_id'],
            ])
            ->assertJsonPath('data.survey_id', $surveyId)
            ->assertJsonPath('data.total', 1);
    }

    #[Test]
    public function test_respond_returns_404_for_nonexistent_survey(): void
    {
        $existingSurveyResponse = $this->withAuth($this->adminToken)->postJson('/api/v1/comunicaciones/surveys', [
            'pregunta' => 'Pregunta',
            'tipo' => 'simple',
            'opciones' => ['A', 'B'],
        ]);

        $optionId = SurveyOption::where('survey_id', $existingSurveyResponse->json('data.id'))->firstOrFail()->id;

        $response = $this->withAuth($this->adminToken)->postJson('/api/v1/comunicaciones/surveys/'.(string) Str::orderedUuid().'/responses', [
            'option_id' => $optionId,
        ]);

        $response->assertNotFound()
            ->assertJsonPath('error.code', 'SURVEY_NOT_FOUND');
    }

    #[Test]
    public function test_results_returns_404_for_nonexistent_survey(): void
    {
        $response = $this->withAuth($this->adminToken)->getJson('/api/v1/comunicaciones/surveys/'.(string) Str::orderedUuid().'/results');

        $response->assertNotFound()
            ->assertJsonPath('error.code', 'SURVEY_NOT_FOUND');
    }
}
