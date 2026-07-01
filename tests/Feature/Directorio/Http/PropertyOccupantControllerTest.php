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

function directorioPropertyOccupantAdminToken(): string
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
    $this->seed(DirectorioSeeder::class);
});

it('lists occupants for a property', function (): void {
    $token = directorioPropertyOccupantAdminToken();
    $property = Property::factory()->create();
    $contact = Contact::factory()->create();
    $occupantType = OccupantType::where('code', 'residente')->first();

    PropertyOccupant::factory()->create([
        'property_id' => $property->id,
        'contact_id' => $contact->id,
        'occupant_type_id' => $occupantType->id,
    ]);

    $response = $this->withHeader('Authorization', "Bearer {$token}")
        ->getJson("/api/v1/properties/{$property->id}/occupants");

    $response->assertOk()
        ->assertJsonStructure([
            'data' => [
                '*' => ['id', 'contact_id', 'occupant_type_id', 'is_primary', 'is_active'],
            ],
            'meta' => ['trace_id'],
        ]);
});

it('creates an occupant for a property', function (): void {
    $token = directorioPropertyOccupantAdminToken();
    $property = Property::factory()->create();
    $contact = Contact::factory()->create();
    $occupantType = OccupantType::where('code', 'residente')->first();

    $response = $this->withHeader('Authorization', "Bearer {$token}")
        ->postJson("/api/v1/properties/{$property->id}/occupants", [
            'contact_id' => $contact->id,
            'occupant_type_id' => $occupantType->id,
            'is_primary' => true,
        ]);

    $response->assertCreated()
        ->assertJsonStructure(['data' => ['id'], 'meta' => ['trace_id']]);

    $this->assertDatabaseHas('property_occupants', [
        'property_id' => $property->id,
        'contact_id' => $contact->id,
        'occupant_type_id' => $occupantType->id,
        'is_primary' => true,
    ]);
});

it('returns 409 when creating duplicate occupant', function (): void {
    $token = directorioPropertyOccupantAdminToken();
    $property = Property::factory()->create();
    $contact = Contact::factory()->create();
    $occupantType = OccupantType::where('code', 'residente')->first();

    PropertyOccupant::factory()->create([
        'property_id' => $property->id,
        'contact_id' => $contact->id,
        'occupant_type_id' => $occupantType->id,
    ]);

    $response = $this->withHeader('Authorization', "Bearer {$token}")
        ->postJson("/api/v1/properties/{$property->id}/occupants", [
            'contact_id' => $contact->id,
            'occupant_type_id' => $occupantType->id,
        ]);

    $response->assertStatus(409)
        ->assertJsonPath('error.code', 'DUPLICATE_OCCUPANT');
});

it('updates an occupant', function (): void {
    $token = directorioPropertyOccupantAdminToken();
    $property = Property::factory()->create();
    $contact = Contact::factory()->create();
    $occupantType = OccupantType::where('code', 'residente')->first();

    $occupant = PropertyOccupant::factory()->create([
        'property_id' => $property->id,
        'contact_id' => $contact->id,
        'occupant_type_id' => $occupantType->id,
        'is_primary' => false,
    ]);

    $response = $this->withHeader('Authorization', "Bearer {$token}")
        ->patchJson("/api/v1/property-occupants/{$occupant->id}", [
            'is_primary' => true,
        ]);

    $response->assertOk()
        ->assertJsonStructure(['meta' => ['trace_id']]);

    $this->assertDatabaseHas('property_occupants', [
        'id' => $occupant->id,
        'is_primary' => true,
    ]);
});

it('deletes an occupant', function (): void {
    $token = directorioPropertyOccupantAdminToken();
    $property = Property::factory()->create();
    $contact = Contact::factory()->create();
    $occupantType = OccupantType::where('code', 'residente')->first();

    $occupant = PropertyOccupant::factory()->create([
        'property_id' => $property->id,
        'contact_id' => $contact->id,
        'occupant_type_id' => $occupantType->id,
    ]);

    $response = $this->withHeader('Authorization', "Bearer {$token}")
        ->deleteJson("/api/v1/property-occupants/{$occupant->id}");

    $response->assertNoContent();
    expect(PropertyOccupant::find($occupant->id))->toBeNull();
});

it('returns 404 when listing occupants for non-existent property', function (): void {
    $token = directorioPropertyOccupantAdminToken();
    $id = (string) Str::uuid();

    $response = $this->withHeader('Authorization', "Bearer {$token}")
        ->getJson("/api/v1/properties/{$id}/occupants");

    $response->assertStatus(404)
        ->assertJsonPath('error.code', 'OCCUPANT_NOT_FOUND');
});
