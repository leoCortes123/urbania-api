<?php

declare(strict_types=1);

namespace Urbania\Propiedades\Infrastructure\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;
use Urbania\Propiedades\Application\DTOs\PropertyStatusResponseDto;

/**
 * @mixin PropertyStatusResponseDto
 */
final class PropertyStatusResource extends JsonResource
{
    /**
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        /** @var PropertyStatusResponseDto $dto */
        $dto = $this->resource;

        return [
            'id' => $dto->id,
            'code' => $dto->code,
            'name' => $dto->name,
            'description' => $dto->description,
            'allows_residents' => $dto->allowsResidents,
            'is_active' => $dto->isActive,
            'sort_order' => $dto->sortOrder,
            'created_at' => $dto->createdAt,
            'updated_at' => $dto->updatedAt,
        ];
    }
}
