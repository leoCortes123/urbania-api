<?php

declare(strict_types=1);

namespace Urbania\Propiedades\Application\DTOs;

final readonly class PaginatedResponseDto
{
    /**
     * @param  array<mixed>  $items
     */
    public function __construct(
        public array $items,
        public int $total,
        public int $page,
        public int $perPage,
        public int $lastPage,
    ) {}
}
