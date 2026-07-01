<?php

declare(strict_types=1);

namespace Urbania\Comunicaciones\Infrastructure\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;
use Urbania\Comunicaciones\Application\DTOs\SurveyListItemDto;

final class SurveyListResource extends JsonResource
{
    /**
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        /** @var array{items: array<SurveyListItemDto>, total: int, page: int, perPage: int, lastPage: int} $dto */
        $dto = $this->resource;

        return [
            'items' => array_map(
                fn (SurveyListItemDto $item) => [
                    'id' => $item->id,
                    'pregunta' => $item->pregunta,
                    'tipo' => $item->tipo,
                    'cierra_el' => $item->cierraEl,
                    'activa' => $item->activa,
                    'opciones_count' => $item->optionsCount,
                    'responses_count' => $item->responsesCount,
                    'created_at' => $item->createdAt,
                ],
                $dto['items'],
            ),
            'total' => $dto['total'],
            'page' => $dto['page'],
            'perPage' => $dto['perPage'],
            'lastPage' => $dto['lastPage'],
        ];
    }
}
