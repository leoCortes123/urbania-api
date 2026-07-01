<?php

declare(strict_types=1);

namespace Urbania\Comunicaciones\Infrastructure\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;
use Urbania\Comunicaciones\Application\DTOs\ChannelDto;

/**
 * @mixin ChannelDto
 */
final class ChannelResource extends JsonResource
{
    /**
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        /** @var ChannelDto $dto */
        $dto = $this->resource;

        return [
            'id' => $dto->id,
            'condominium_id' => $dto->condominiumId,
            'canal' => $dto->canal,
            'provider' => $dto->provider,
            'activo' => $dto->activo,
            'config_mask' => $dto->configMask,
        ];
    }
}
