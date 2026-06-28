<?php

declare(strict_types=1);

namespace Urbania\Propiedades\Infrastructure\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;
use Urbania\Propiedades\Application\DTOs\PropertyDocumentResponseDto;

/**
 * @mixin PropertyDocumentResponseDto
 */
final class PropertyDocumentResource extends JsonResource
{
    /**
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        /** @var PropertyDocumentResponseDto $dto */
        $dto = $this->resource;

        return [
            'id' => $dto->id,
            'property_id' => $dto->propertyId,
            'name' => $dto->name,
            'file_url' => $dto->fileUrl,
            'file_size_bytes' => $dto->fileSizeBytes,
            'mime_type' => $dto->mimeType,
            'notes' => $dto->notes,
            'document_type' => $dto->documentType,
            'uploaded_by' => $dto->uploadedBy,
            'created_at' => $dto->createdAt,
        ];
    }
}
