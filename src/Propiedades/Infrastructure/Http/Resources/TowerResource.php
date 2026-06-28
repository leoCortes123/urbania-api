<?php

declare(strict_types=1);

namespace Urbania\Propiedades\Infrastructure\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;
use Urbania\Propiedades\Application\DTOs\TowerResponseDto;

/**
 * @mixin TowerResponseDto
 */
final class TowerResource extends JsonResource
{
    /**
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        /** @var TowerResponseDto $dto */
        $dto = $this->resource;

        return [
            'id' => $dto->id,
            'condominium_id' => $dto->condominiumId,
            'name' => $dto->name,
            'code' => $dto->code,
            'floor_count' => $dto->floorCount,
            'has_elevator' => $dto->hasElevator,
            'description' => $dto->description,
            'sort_order' => $dto->sortOrder,
            'created_at' => $dto->createdAt,
            'updated_at' => $dto->updatedAt,
        ];
    }
}
