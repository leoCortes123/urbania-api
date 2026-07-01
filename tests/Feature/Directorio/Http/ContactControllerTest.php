<?php

declare(strict_types=1);

use App\Models\Contact;
use App\Models\OccupantType;
use App\Models\Property;
use App\Models\PropertyOccupant;
use App\Models\User;
use Database\Seeders\DirectorioSeeder;
use Illuminate\Support\Facades\Redis;
use Illuminate\Support\Str;
use Urbania\Auth\Application\Services\JwtServiceInterface;
use Urbania\Auth\Domain\ValueObjects\SessionId;

function directorioAdminToken(): string
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

it('lists contacts with pagination metadata', function (): void {
    $token = directorioAdminToken();
    Contact::factory()->count(3)->create();

    $response = $this->withHeader('Authorization', "Bearer {$token}")
        ->getJson('/api/v1/contacts');

    $response->assertOk()
        ->assertJsonStructure([
            'data' => [
                '*' => ['id', 'full_name', 'document_type', 'document_number', 'email', 'phone'],
            ],
            'meta' => ['trace_id'],
        ]);
});

it('creates a contact', function (): void {
    $token = directorioAdminToken();

    $response = $this->withHeader('Authorization', "Bearer {$token}")
        ->postJson('/api/v1/contacts', [
            'full_name' => 'Juan Pérez',
            'document_type' => 'CC',
            'document_number' => '12345678',
            'email' => 'juan@example.com',
            'phone' => '3001234567',
        ]);

    $response->assertCreated()
        ->assertJsonStructure(['data' => ['id'], 'meta' => ['trace_id']]);

    $this->assertDatabaseHas('contacts', [
        'full_name' => 'Juan Pérez',
        'document_type' => 'CC',
        'document_number' => '12345678',
    ]);
});

it('returns 409 when creating contact with duplicate document', function (): void {
    $token = directorioAdminToken();
    Contact::factory()->create([
        'document_type' => 'CC',
        'document_number' => '12345678',
    ]);

    $response = $this->withHeader('Authorization', "Bearer {$token}")
        ->postJson('/api/v1/contacts', [
            'full_name' => 'Otro Nombre',
            'document_type' => 'CC',
            'document_number' => '12345678',
            'email' => 'otro@example.com',
        ]);

    $response->assertStatus(409)
        ->assertJsonPath('error.code', 'DUPLICATE_CONTACT_DOCUMENT');
});

it('shows a contact by id', function (): void {
    $token = directorioAdminToken();
    $contact = Contact::factory()->create();

    $response = $this->withHeader('Authorization', "Bearer {$token}")
        ->getJson("/api/v1/contacts/{$contact->id}");

    $response->assertOk()
        ->assertJsonPath('data.id', $contact->id)
        ->assertJsonStructure(['data' => ['properties', 'created_at', 'updated_at']]);
});

it('returns 404 when showing non-existent contact', function (): void {
    $token = directorioAdminToken();
    $id = (string) Str::uuid();

    $response = $this->withHeader('Authorization', "Bearer {$token}")
        ->getJson("/api/v1/contacts/{$id}");

    $response->assertStatus(404)
        ->assertJsonPath('error.code', 'CONTACT_NOT_FOUND');
});

it('updates a contact', function (): void {
    $token = directorioAdminToken();
    $contact = Contact::factory()->create();

    $response = $this->withHeader('Authorization', "Bearer {$token}")
        ->patchJson("/api/v1/contacts/{$contact->id}", [
            'full_name' => 'Nombre Actualizado',
            'phone' => '3009876543',
        ]);

    $response->assertOk()
        ->assertJsonStructure(['meta' => ['trace_id']]);

    $this->assertDatabaseHas('contacts', [
        'id' => $contact->id,
        'full_name' => 'Nombre Actualizado',
        'phone' => '3009876543',
    ]);
});

it('returns 409 when updating to existing document', function (): void {
    $token = directorioAdminToken();
    $contact = Contact::factory()->create();
    $other = Contact::factory()->create([
        'document_type' => 'CC',
        'document_number' => '87654321',
    ]);

    $response = $this->withHeader('Authorization', "Bearer {$token}")
        ->patchJson("/api/v1/contacts/{$contact->id}", [
            'document_type' => 'CC',
            'document_number' => '87654321',
        ]);

    $response->assertStatus(409)
        ->assertJsonPath('error.code', 'DUPLICATE_CONTACT_DOCUMENT');

    $this->assertDatabaseHas('contacts', [
        'id' => $contact->id,
        'document_number' => $contact->document_number,
    ]);
});

it('deletes a contact', function (): void {
    $token = directorioAdminToken();
    $contact = Contact::factory()->create();

    $response = $this->withHeader('Authorization', "Bearer {$token}")
        ->deleteJson("/api/v1/contacts/{$contact->id}");

    $response->assertNoContent();
    expect(Contact::find($contact->id))->toBeNull();
});

it('returns 409 when deleting contact with active occupants', function (): void {
    $token = directorioAdminToken();
    $this->seed(DirectorioSeeder::class);

    $contact = Contact::factory()->create();
    $property = Property::factory()->create();
    $occupantType = OccupantType::where('code', 'propietario')->first();

    PropertyOccupant::factory()->create([
        'property_id' => $property->id,
        'contact_id' => $contact->id,
        'occupant_type_id' => $occupantType->id,
        'is_active' => true,
    ]);

    $response = $this->withHeader('Authorization', "Bearer {$token}")
        ->deleteJson("/api/v1/contacts/{$contact->id}");

    $response->assertStatus(409)
        ->assertJsonPath('error.code', 'CONTACT_HAS_ACTIVE_OCCUPANTS');
});

it('lists properties for a contact', function (): void {
    $token = directorioAdminToken();
    $this->seed(DirectorioSeeder::class);

    $contact = Contact::factory()->create();
    $property = Property::factory()->create();
    $occupantType = OccupantType::where('code', 'residente')->first();

    PropertyOccupant::factory()->create([
        'property_id' => $property->id,
        'contact_id' => $contact->id,
        'occupant_type_id' => $occupantType->id,
    ]);

    $response = $this->withHeader('Authorization', "Bearer {$token}")
        ->getJson("/api/v1/contacts/{$contact->id}/properties");

    $response->assertOk()
        ->assertJsonStructure([
            'data' => [
                '*' => ['property_id', 'occupant_type_id', 'is_primary', 'is_active'],
            ],
            'meta' => ['trace_id'],
        ]);
});
