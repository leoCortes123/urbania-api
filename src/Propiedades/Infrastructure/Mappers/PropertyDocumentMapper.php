<?php

declare(strict_types=1);

namespace Urbania\Propiedades\Infrastructure\Mappers;

use App\Models\PropertyDocument as PropertyDocumentModel;
use Urbania\Propiedades\Domain\Entities\PropertyDocumentEntity;
use Urbania\Shared\Domain\ValueObjects\Uuid;

final readonly class PropertyDocumentMapper
{
    public function toDomain(PropertyDocumentModel $model): PropertyDocumentEntity
    {
        return PropertyDocumentEntity::reconstitute(
            id: Uuid::fromString($model->id),
            propertyId: Uuid::fromString($model->property_id),
            propertyDocumentTypeId: Uuid::fromString($model->property_document_type_id),
            name: $model->name,
            fileUrl: $model->file_url,
            fileSizeBytes: $model->file_size_bytes,
            mimeType: $model->mime_type,
            notes: $model->notes,
            uploadedByUserId: Uuid::fromString($model->uploaded_by_user_id),
            createdAt: $this->toDateTimeImmutable($model->created_at),
            deletedAt: $model->deleted_at === null ? null : $this->toDateTimeImmutable($model->deleted_at),
        );
    }

    /**
     * @return array<string, mixed>
     */
    public function toPersistence(PropertyDocumentEntity $entity): array
    {
        return [
            'id' => $entity->id()->toString(),
            'property_id' => $entity->propertyId()->toString(),
            'property_document_type_id' => $entity->propertyDocumentTypeId()->toString(),
            'name' => $entity->name(),
            'file_url' => $entity->fileUrl(),
            'file_size_bytes' => $entity->fileSizeBytes(),
            'mime_type' => $entity->mimeType(),
            'notes' => $entity->notes(),
            'uploaded_by_user_id' => $entity->uploadedByUserId()->toString(),
            'created_at' => $entity->createdAt()->format('Y-m-d H:i:s'),
        ];
    }

    /**
     * @param  array<PropertyDocumentModel>  $models
     * @return array<PropertyDocumentEntity>
     */
    public function toDomainArray(array $models): array
    {
        return array_map(fn (PropertyDocumentModel $model): PropertyDocumentEntity => $this->toDomain($model), $models);
    }

    private function toDateTimeImmutable(mixed $value): \DateTimeImmutable
    {
        assert($value instanceof \DateTimeInterface);

        return \DateTimeImmutable::createFromMutable($value instanceof \DateTime ? $value : \DateTime::createFromImmutable($value));
    }
}
