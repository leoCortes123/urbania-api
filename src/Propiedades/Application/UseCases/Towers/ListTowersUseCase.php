<?php

declare(strict_types=1);

namespace Urbania\Propiedades\Application\UseCases\Towers;

use Urbania\Propiedades\Application\DTOs\PaginatedResponseDto;
use Urbania\Propiedades\Application\DTOs\TowerResponseDto;
use Urbania\Propiedades\Domain\Exceptions\CondominiumNotFoundException;
use Urbania\Propiedades\Domain\Repositories\CondominiumRepositoryInterface;
use Urbania\Propiedades\Domain\Repositories\TowerRepositoryInterface;
use Urbania\Shared\Domain\ValueObjects\Uuid;

final readonly class ListTowersUseCase
{
    public function __construct(
        private TowerRepositoryInterface $towerRepository,
        private CondominiumRepositoryInterface $condominiumRepository,
    ) {}

    /**
     * @param  array<string, mixed>  $filters
     */
    public function execute(string $condominiumId, array $filters = [], int $page = 1, int $perPage = 20): PaginatedResponseDto
    {
        $uuid = Uuid::fromString($condominiumId);

        if ($this->condominiumRepository->findById($uuid) === null) {
            throw new CondominiumNotFoundException;
        }

        $result = $this->towerRepository->findByCondominiumId($uuid, $filters, $page, $perPage);

        return new PaginatedResponseDto(
            items: array_map(
                fn ($entity) => TowerResponseDto::fromEntity($entity),
                $result['items']
            ),
            total: $result['total'],
            page: $result['page'],
            perPage: $result['perPage'],
            lastPage: $result['lastPage'],
        );
    }
}
