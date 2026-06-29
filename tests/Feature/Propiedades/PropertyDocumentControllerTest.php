<?php

declare(strict_types=1);

use App\Models\Property;
use App\Models\PropertyDocument;
use App\Models\PropertyDocumentType;
use App\Models\User;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Redis;
use Illuminate\Support\Facades\Storage;
use Urbania\Auth\Application\Services\JwtServiceInterface;
use Urbania\Auth\Domain\ValueObjects\SessionId;

function documentAdminToken(): string
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
    Storage::fake('public');
});

it('lists property documents', function (): void {
    $token = documentAdminToken();
    $property = Property::factory()->create();
    PropertyDocument::factory()->count(3)->create(['property_id' => $property->id]);

    $response = $this->withHeader('Authorization', "Bearer {$token}")
        ->getJson("/api/v1/properties/{$property->id}/documents");

    $response->assertOk()
        ->assertJsonCount(3, 'data')
        ->assertJsonStructure([
            'data' => [
                '*' => ['id', 'property_id', 'name', 'file_url', 'document_type', 'uploaded_by'],
            ],
            'meta' => ['trace_id', 'current_page', 'per_page', 'total', 'last_page'],
        ]);
});

it('uploads a property document', function (): void {
    $token = documentAdminToken();
    $property = Property::factory()->create();
    $type = PropertyDocumentType::factory()->create();
    $file = UploadedFile::fake()->create('document.pdf', 1024, 'application/pdf');

    $response = $this->withHeader('Authorization', "Bearer {$token}")
        ->postJson("/api/v1/properties/{$property->id}/documents", [
            'property_document_type_id' => $type->id,
            'name' => 'Documento de prueba',
            'file' => $file,
        ]);

    $response->assertCreated()
        ->assertJsonPath('data.name', 'Documento de prueba')
        ->assertJsonPath('data.property_id', $property->id);
});

it('deletes a property document', function (): void {
    $token = documentAdminToken();
    $document = PropertyDocument::factory()->create();

    $response = $this->withHeader('Authorization', "Bearer {$token}")
        ->deleteJson("/api/v1/properties/{$document->property_id}/documents/{$document->id}");

    $response->assertNoContent();
    expect(PropertyDocument::find($document->id))->toBeNull();
});
