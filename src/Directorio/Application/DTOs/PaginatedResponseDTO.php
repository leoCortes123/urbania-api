<?php

declare(strict_types=1);

namespace Urbania\Directorio\Application\DTOs;

final readonly class PaginatedResponseDTO
{
    /**
     * @param  array<int, mixed>  $data
     */
    public function __construct(
        public array $data,
        public int $currentPage,
        public int $perPage,
        public int $total,
        public int $lastPage,
    ) {}
}
