<?php

declare(strict_types=1);

namespace Urbania\Propiedades\Infrastructure\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;
use Urbania\Propiedades\Application\DTOs\PropertyResponseDto;

/**
 * @mixin PropertyResponseDto
 */
final class PropertyResource extends JsonResource
{
    /**
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        /** @var PropertyResponseDto $dto */
        $dto = $this->resource;

        return [
            'id' => $dto->id,
            'condominium_id' => $dto->condominiumId,
            'tower_id' => $dto->towerId,
            'property_type_id' => $dto->propertyTypeId,
            'property_status_id' => $dto->propertyStatusId,
            'floor' => $dto->floor,
            'unit_number' => $dto->unitNumber,
            'area_m2' => $dto->areaM2,
            'coefficient' => $dto->coefficient,
            'bedrooms' => $dto->bedrooms,
            'bathrooms' => $dto->bathrooms,
            'has_parking' => $dto->hasParking,
            'parking_lot' => $dto->parkingLot,
            'notes' => $dto->notes,
            'tower' => $dto->tower,
            'type' => $dto->type,
            'status' => $dto->status,
            'full_designation' => $dto->fullDesignation,
            'residents_count' => $dto->residentsCount,
            'documents_count' => $dto->documentsCount,
            'created_at' => $dto->createdAt,
            'updated_at' => $dto->updatedAt,
        ];
    }
}
