<?php

declare(strict_types=1);

namespace Urbania\Propiedades\Application\DTOs;

use Urbania\Propiedades\Domain\Entities\PropertyDocumentEntity;

final readonly class PropertyDocumentResponseDto
{
    /**
     * @param  array<string, mixed>  $documentType
     * @param  array<string, mixed>  $uploadedBy
     */
    public function __construct(
        public string $id,
        public string $propertyId,
        public string $name,
        public string $fileUrl,
        public ?int $fileSizeBytes,
        public ?string $mimeType,
        public ?string $notes,
        public array $documentType,
        public array $uploadedBy,
        public string $createdAt,
    ) {}

    public static function fromEntity(PropertyDocumentEntity $entity): self
    {
        return new self(
            id: $entity->id()->toString(),
            propertyId: $entity->propertyId()->toString(),
            name: $entity->name(),
            fileUrl: $entity->fileUrl(),
            fileSizeBytes: $entity->fileSizeBytes(),
            mimeType: $entity->mimeType(),
            notes: $entity->notes(),
            documentType: ['id' => $entity->propertyDocumentTypeId()->toString()],
            uploadedBy: ['id' => $entity->uploadedByUserId()->toString()],
            createdAt: $entity->createdAt()->format('c'),
        );
    }
}
