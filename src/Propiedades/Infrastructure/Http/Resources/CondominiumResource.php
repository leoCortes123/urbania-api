<?php

declare(strict_types=1);

namespace Urbania\Propiedades\Infrastructure\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;
use Urbania\Propiedades\Application\DTOs\CondominiumResponseDto;

/**
 * @mixin CondominiumResponseDto
 */
final class CondominiumResource extends JsonResource
{
    /**
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        /** @var CondominiumResponseDto $dto */
        $dto = $this->resource;

        return [
            'id' => $dto->id,
            'name' => $dto->name,
            'address' => $dto->address,
            'city' => $dto->city,
            'department' => $dto->department,
            'country' => $dto->country,
            'nit' => $dto->nit,
            'phone' => $dto->phone,
            'email' => $dto->email,
            'legal_representative' => $dto->legalRepresentative,
            'total_coefficient' => $dto->totalCoefficient,
            'logo_url' => $dto->logoUrl,
            'is_active' => $dto->isActive,
            'created_at' => $dto->createdAt,
            'updated_at' => $dto->updatedAt,
        ];
    }
}
