<?php

declare(strict_types=1);

namespace Urbania\Propiedades\Application\UseCases\Properties;

use Urbania\Propiedades\Application\DTOs\PaginatedResponseDto;
use Urbania\Propiedades\Application\DTOs\PropertyResponseDto;
use Urbania\Propiedades\Domain\Repositories\PropertyRepositoryInterface;

final readonly class ListPropertiesUseCase
{
    public function __construct(
        private PropertyRepositoryInterface $repository,
    ) {}

    /**
     * @param  array<string, mixed>  $filters
     */
    public function execute(array $filters = [], int $page = 1, int $perPage = 20): PaginatedResponseDto
    {
        $result = $this->repository->findAll($filters, $page, $perPage);

        return new PaginatedResponseDto(
            items: array_map(
                fn ($entity) => PropertyResponseDto::fromEntity($entity),
                $result['items']
            ),
            total: $result['total'],
            page: $result['page'],
            perPage: $result['perPage'],
            lastPage: $result['lastPage'],
        );
    }
}
