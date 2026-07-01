<?php

declare(strict_types=1);

namespace Urbania\Comunicaciones\Infrastructure\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;
use Urbania\Comunicaciones\Application\DTOs\TemplateDto;

/**
 * @mixin TemplateDto
 */
final class TemplateResource extends JsonResource
{
    /**
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        /** @var TemplateDto $dto */
        $dto = $this->resource;

        return [
            'id' => $dto->id,
            'condominium_id' => $dto->condominiumId,
            'nombre' => $dto->nombre,
            'tipo' => $dto->tipo,
            'cuerpo' => $dto->cuerpo,
            'created_at' => $dto->createdAt,
            'updated_at' => $dto->updatedAt,
        ];
    }
}
