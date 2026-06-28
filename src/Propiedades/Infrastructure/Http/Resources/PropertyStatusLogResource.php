<?php

declare(strict_types=1);

namespace Urbania\Propiedades\Infrastructure\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;
use Urbania\Propiedades\Application\DTOs\PropertyStatusLogResponseDto;

/**
 * @mixin PropertyStatusLogResponseDto
 */
final class PropertyStatusLogResource extends JsonResource
{
    /**
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        /** @var PropertyStatusLogResponseDto $dto */
        $dto = $this->resource;

        return [
            'id' => $dto->id,
            'property_id' => $dto->propertyId,
            'from_status' => $dto->fromStatus,
            'to_status' => $dto->toStatus,
            'changed_by' => $dto->changedBy,
            'reason' => $dto->reason,
            'created_at' => $dto->createdAt,
        ];
    }
}
