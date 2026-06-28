<?php

declare(strict_types=1);

namespace Urbania\Propiedades\Infrastructure\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;
use Urbania\Propiedades\Application\DTOs\PaginatedResponseDto;

/**
 * @mixin PaginatedResponseDto
 */
final class TowerCollection extends JsonResource
{
    public function __construct(PaginatedResponseDto $resource)
    {
        parent::__construct($resource);
    }

    /**
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        /** @var PaginatedResponseDto $dto */
        $dto = $this->resource;

        return [
            'data' => array_map(
                fn ($item) => (new TowerResource($item))->resolve($request),
                $dto->items
            ),
            'meta' => [
                'current_page' => $dto->page,
                'per_page' => $dto->perPage,
                'total' => $dto->total,
                'last_page' => $dto->lastPage,
            ],
        ];
    }
}
