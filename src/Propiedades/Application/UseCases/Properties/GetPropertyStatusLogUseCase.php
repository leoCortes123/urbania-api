<?php

declare(strict_types=1);

namespace Urbania\Propiedades\Application\UseCases\Properties;

use Urbania\Propiedades\Application\DTOs\PaginatedResponseDto;
use Urbania\Propiedades\Application\DTOs\PropertyStatusLogResponseDto;
use Urbania\Propiedades\Domain\Exceptions\PropertyNotFoundException;
use Urbania\Propiedades\Domain\Repositories\PropertyRepositoryInterface;
use Urbania\Propiedades\Domain\Repositories\PropertyStatusLogRepositoryInterface;
use Urbania\Shared\Domain\ValueObjects\Uuid;

final readonly class GetPropertyStatusLogUseCase
{
    public function __construct(
        private PropertyRepositoryInterface $propertyRepository,
        private PropertyStatusLogRepositoryInterface $statusLogRepository,
    ) {}

    public function execute(string $propertyId, int $page = 1, int $perPage = 20): PaginatedResponseDto
    {
        $uuid = Uuid::fromString($propertyId);

        if ($this->propertyRepository->findById($uuid) === null) {
            throw new PropertyNotFoundException;
        }

        $result = $this->statusLogRepository->findByPropertyId($uuid, $page, $perPage);

        return new PaginatedResponseDto(
            items: array_map(
                fn ($entity) => PropertyStatusLogResponseDto::fromEntity($entity),
                $result['items']
            ),
            total: $result['total'],
            page: $result['page'],
            perPage: $result['perPage'],
            lastPage: $result['lastPage'],
        );
    }
}
