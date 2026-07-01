<?php

declare(strict_types=1);

namespace Urbania\Comunicaciones\Infrastructure\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;
use Urbania\Comunicaciones\Application\DTOs\AnnouncementDto;

/**
 * @mixin AnnouncementDto
 */
final class AnnouncementResource extends JsonResource
{
    /**
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        /** @var AnnouncementDto $dto */
        $dto = $this->resource;

        return [
            'id' => $dto->id,
            'condominium_id' => $dto->condominiumId,
            'autor_user_id' => $dto->autorUserId,
            'titulo' => $dto->titulo,
            'cuerpo' => $dto->cuerpo,
            'segmento' => $dto->segmento,
            'target_id' => $dto->targetId,
            'estado' => $dto->estado,
            'programado_para' => $dto->programadoPara,
            'fijado' => $dto->fijado,
            'canales' => $dto->canales,
            'created_at' => $dto->createdAt,
            'updated_at' => $dto->updatedAt,
        ];
    }
}
