<?php

declare(strict_types=1);

namespace Directorio\Infrastructure\Http\Controllers;

use App\Http\Controllers\Controller;
use Directorio\Application\DTOs\CreateContactDTO;
use Directorio\Application\DTOs\UpdateContactDTO;
use Directorio\Application\UseCases\Contacts\CreateContactUseCase;
use Directorio\Application\UseCases\Contacts\DeleteContactUseCase;
use Directorio\Application\UseCases\Contacts\GetContactPropertiesUseCase;
use Directorio\Application\UseCases\Contacts\GetContactUseCase;
use Directorio\Application\UseCases\Contacts\ListContactsUseCase;
use Directorio\Application\UseCases\Contacts\UpdateContactUseCase;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class ContactController extends Controller
{
    public function __construct(
        private readonly ListContactsUseCase $listContactsUseCase,
        private readonly GetContactUseCase $getContactUseCase,
        private readonly CreateContactUseCase $createContactUseCase,
        private readonly UpdateContactUseCase $updateContactUseCase,
        private readonly DeleteContactUseCase $deleteContactUseCase,
        private readonly GetContactPropertiesUseCase $getContactPropertiesUseCase,
    ) {}

    public function index(Request $request): JsonResponse
    {
        /** @var array<string, mixed> $filters */
        $filters = array_filter($request->only(['full_name', 'document_type', 'document_number', 'email', 'phone']));
        $contacts = $this->listContactsUseCase->execute($filters);

        $data = array_map(fn ($c) => [
            'id' => $c->id(),
            'full_name' => $c->fullName(),
            'document_type' => $c->documentType()->value(),
            'document_number' => $c->documentNumber()->value(),
            'email' => $c->email(),
            'phone' => $c->phone(),
            'emergency_contact_name' => $c->emergencyContactName(),
            'emergency_contact_phone' => $c->emergencyContactPhone(),
            'notes' => $c->notes(),
            'user_id' => $c->userId(),
            'created_at' => $c->createdAt(),
            'updated_at' => $c->updatedAt(),
        ], $contacts);

        return response()->json([
            'data' => array_values($data),
            'meta' => ['trace_id' => request()->header('X-Trace-Id', '')],
        ]);
    }

    public function show(string $id): JsonResponse
    {
        $contact = $this->getContactUseCase->execute($id);
        $properties = $this->getContactPropertiesUseCase->execute($id);

        $propertiesData = array_map(fn ($o) => [
            'property_id' => $o->propertyId(),
            'occupant_type_id' => $o->occupantTypeId(),
            'is_primary' => $o->isPrimary(),
            'is_active' => $o->isActive(),
            'move_in_date' => $o->moveInDate(),
            'move_out_date' => $o->moveOutDate(),
        ], $properties);

        return response()->json([
            'data' => [
                'id' => $contact->id(),
                'full_name' => $contact->fullName(),
                'document_type' => $contact->documentType()->value(),
                'document_number' => $contact->documentNumber()->value(),
                'email' => $contact->email(),
                'phone' => $contact->phone(),
                'emergency_contact_name' => $contact->emergencyContactName(),
                'emergency_contact_phone' => $contact->emergencyContactPhone(),
                'notes' => $contact->notes(),
                'user_id' => $contact->userId(),
                'properties' => $propertiesData,
                'created_at' => $contact->createdAt(),
                'updated_at' => $contact->updatedAt(),
            ],
            'meta' => ['trace_id' => request()->header('X-Trace-Id', '')],
        ]);
    }

    public function store(Request $request): JsonResponse
    {
        $dto = new CreateContactDTO(
            fullName: self::stringValue($request->input('full_name')),
            documentType: self::stringValue($request->input('document_type')),
            documentNumber: self::stringValue($request->input('document_number')),
            email: self::nullableString($request->input('email')),
            phone: self::nullableString($request->input('phone')),
            emergencyContactName: self::nullableString($request->input('emergency_contact_name')),
            emergencyContactPhone: self::nullableString($request->input('emergency_contact_phone')),
            notes: self::nullableString($request->input('notes')),
            userId: self::nullableString($request->input('user_id')),
        );

        $contact = $this->createContactUseCase->execute($dto);

        return response()->json([
            'data' => ['id' => $contact->id()],
            'meta' => ['trace_id' => $request->header('X-Trace-Id', '')],
        ], 201);
    }

    public function update(Request $request, string $id): JsonResponse
    {
        $dto = new UpdateContactDTO(
            fullName: self::nullableString($request->input('full_name')),
            documentType: self::nullableString($request->input('document_type')),
            documentNumber: self::nullableString($request->input('document_number')),
            email: self::nullableString($request->input('email')),
            phone: self::nullableString($request->input('phone')),
            emergencyContactName: self::nullableString($request->input('emergency_contact_name')),
            emergencyContactPhone: self::nullableString($request->input('emergency_contact_phone')),
            notes: self::nullableString($request->input('notes')),
            userId: self::nullableString($request->input('user_id')),
        );

        $this->updateContactUseCase->execute($id, $dto);

        return response()->json(['meta' => ['trace_id' => $request->header('X-Trace-Id', '')]]);
    }

    public function destroy(string $id): JsonResponse
    {
        $this->deleteContactUseCase->execute($id);

        return response()->json(null, 204);
    }

    public function properties(string $id): JsonResponse
    {
        $occupants = $this->getContactPropertiesUseCase->execute($id);

        $data = array_map(fn ($o) => [
            'property_id' => $o->propertyId(),
            'occupant_type_id' => $o->occupantTypeId(),
            'is_primary' => $o->isPrimary(),
            'is_active' => $o->isActive(),
            'move_in_date' => $o->moveInDate(),
            'move_out_date' => $o->moveOutDate(),
        ], $occupants);

        return response()->json([
            'data' => array_values($data),
            'meta' => ['trace_id' => request()->header('X-Trace-Id', '')],
        ]);
    }

    private static function stringValue(mixed $value): string
    {
        return is_string($value) ? $value : (is_scalar($value) ? (string) $value : '');
    }

    private static function nullableString(mixed $value): ?string
    {
        return is_string($value) ? $value : (is_scalar($value) ? (string) $value : null);
    }
}
