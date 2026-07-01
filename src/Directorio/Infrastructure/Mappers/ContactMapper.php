<?php

declare(strict_types=1);

namespace Directorio\Infrastructure\Mappers;

use App\Models\Contact as EloquentContact;
use Directorio\Domain\Entities\Contact;
use Directorio\Domain\ValueObjects\DocumentNumber;
use Directorio\Domain\ValueObjects\DocumentType;

class ContactMapper
{
    public static function toDomain(EloquentContact $model): Contact
    {
        return new Contact(
            id: $model->id,
            documentType: new DocumentType($model->document_type),
            documentNumber: new DocumentNumber($model->document_number),
            fullName: $model->full_name,
            email: $model->email,
            phone: $model->phone,
            emergencyContactName: $model->emergency_contact_name,
            emergencyContactPhone: $model->emergency_contact_phone,
            notes: $model->notes,
            userId: $model->user_id,
            organizationId: $model->organization_id,
            createdAt: $model->created_at?->toISOString(),
            updatedAt: $model->updated_at?->toISOString(),
            deletedAt: $model->deleted_at?->toISOString(),
        );
    }

    /**
     * @param  EloquentContact[]  $models
     * @return Contact[]
     */
    public static function toDomainArray(array $models): array
    {
        return array_map(fn (EloquentContact $m) => self::toDomain($m), $models);
    }

    /** @return array<string, mixed> */
    public static function toPersistence(Contact $contact): array
    {
        return [
            'id' => $contact->id(),
            'user_id' => $contact->userId(),
            'document_type' => $contact->documentType()->value(),
            'document_number' => $contact->documentNumber()->value(),
            'full_name' => $contact->fullName(),
            'email' => $contact->email(),
            'phone' => $contact->phone(),
            'emergency_contact_name' => $contact->emergencyContactName(),
            'emergency_contact_phone' => $contact->emergencyContactPhone(),
            'notes' => $contact->notes(),
            'organization_id' => $contact->organizationId(),
        ];
    }
}
