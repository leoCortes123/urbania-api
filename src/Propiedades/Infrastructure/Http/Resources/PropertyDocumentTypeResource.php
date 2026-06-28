<?php

declare(strict_types=1);

namespace Urbania\Propiedades\Infrastructure\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;
use Urbania\Propiedades\Application\DTOs\PropertyDocumentTypeResponseDto;

/**
 * @mixin PropertyDocumentTypeResponseDto
 */
final class PropertyDocumentTypeResource extends JsonResource
{
    /**
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        /** @var PropertyDocumentTypeResponseDto $dto */
        $dto = $this->resource;

        return [
            'id' => $dto->id,
            'code' => $dto->code,
            'name' => $dto->name,
            'description' => $dto->description,
            'sort_order' => $dto->sortOrder,
            'is_active' => $dto->isActive,
            'created_at' => $dto->createdAt,
            'updated_at' => $dto->updatedAt,
        ];
    }
}
