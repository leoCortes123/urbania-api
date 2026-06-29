<?php

declare(strict_types=1);

use App\Models\PropertyDocument;
use App\Models\PropertyDocumentType;
use App\Models\User;
use Illuminate\Support\Facades\Redis;
use Urbania\Auth\Application\Services\JwtServiceInterface;
use Urbania\Auth\Domain\ValueObjects\SessionId;

function documentTypeAdminToken(): string
{
    $user = User::factory()->admin()->create();
    $service = app(JwtServiceInterface::class);

    return $service->generateAccessToken(
        userId: $user->id,
        role: 'admin',
        mfaVerified: false,
        sessionId: SessionId::generate(),
        deviceFingerprint: '',
        organizationId: $user->organization_id,
    )->toString();
}

beforeEach(function (): void {
    Redis::flushall();
});

it('lists property document types', function (): void {
    $token = documentTypeAdminToken();
    PropertyDocumentType::factory()->count(3)->create();

    $response = $this->withHeader('Authorization', "Bearer {$token}")
        ->getJson('/api/v1/property-document-types');

    $response->assertOk()
        ->assertJsonStructure([
            'data' => [
                '*' => ['id', 'code', 'name', 'description', 'sort_order', 'is_active'],
            ],
            'meta' => ['trace_id', 'current_page', 'per_page', 'total', 'last_page'],
        ]);
});

it('creates a property document type', function (): void {
    $token = documentTypeAdminToken();

    $response = $this->withHeader('Authorization', "Bearer {$token}")
        ->postJson('/api/v1/property-document-types', [
            'code' => 'acta',
            'name' => 'Acta de propiedad',
            'sort_order' => 1,
        ]);

    $response->assertCreated()
        ->assertJsonPath('data.code', 'acta')
        ->assertJsonPath('data.is_active', true);
});

it('updates a property document type', function (): void {
    $token = documentTypeAdminToken();
    $type = PropertyDocumentType::factory()->create();

    $response = $this->withHeader('Authorization', "Bearer {$token}")
        ->patchJson("/api/v1/property-document-types/{$type->id}", [
            'name' => 'Updated Document Type',
        ]);

    $response->assertOk()
        ->assertJsonPath('data.name', 'Updated Document Type');
});

it('deactivates a property document type', function (): void {
    $token = documentTypeAdminToken();
    $type = PropertyDocumentType::factory()->create();

    $response = $this->withHeader('Authorization', "Bearer {$token}")
        ->deleteJson("/api/v1/property-document-types/{$type->id}");

    $response->assertNoContent();
    expect(PropertyDocumentType::find($type->id)->is_active)->toBeFalse();
});

it('returns 409 when deleting document type in use', function (): void {
    $token = documentTypeAdminToken();
    $type = PropertyDocumentType::factory()->create();
    PropertyDocument::factory()->create(['property_document_type_id' => $type->id]);

    $response = $this->withHeader('Authorization', "Bearer {$token}")
        ->deleteJson("/api/v1/property-document-types/{$type->id}");

    $response->assertStatus(409)
        ->assertJsonPath('error.code', 'PROPERTY_DOCUMENT_TYPE_IN_USE');
});
