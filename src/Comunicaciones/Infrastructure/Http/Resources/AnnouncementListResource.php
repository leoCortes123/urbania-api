<?php

declare(strict_types=1);

namespace Urbania\Comunicaciones\Infrastructure\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;
use Urbania\Comunicaciones\Application\DTOs\AnnouncementListItemDto;

final class AnnouncementListResource extends JsonResource
{
    /**
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        /** @var array{items: array<AnnouncementListItemDto>, total: int, page: int, perPage: int, lastPage: int} $dto */
        $dto = $this->resource;

        return [
            'items' => array_map(
                fn (AnnouncementListItemDto $item) => [
                    'id' => $item->id,
                    'titulo' => $item->titulo,
                    'segmento' => $item->segmento,
                    'estado' => $item->estado,
                    'programado_para' => $item->programadoPara,
                    'fijado' => $item->fijado,
                    'metrics' => $item->metrics,
                ],
                $dto['items'],
            ),
            'meta' => [
                'total' => $dto['total'],
                'current_page' => $dto['page'],
                'per_page' => $dto['perPage'],
                'last_page' => $dto['lastPage'],
            ],
        ];
    }
}
