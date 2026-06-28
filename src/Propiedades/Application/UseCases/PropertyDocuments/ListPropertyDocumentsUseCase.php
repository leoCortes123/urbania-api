<?php

declare(strict_types=1);

namespace Urbania\Propiedades\Application\UseCases\PropertyDocuments;

use Urbania\Propiedades\Application\DTOs\PaginatedResponseDto;
use Urbania\Propiedades\Application\DTOs\PropertyDocumentResponseDto;
use Urbania\Propiedades\Domain\Exceptions\PropertyNotFoundException;
use Urbania\Propiedades\Domain\Repositories\PropertyDocumentRepositoryInterface;
use Urbania\Propiedades\Domain\Repositories\PropertyRepositoryInterface;
use Urbania\Shared\Domain\ValueObjects\Uuid;

final readonly class ListPropertyDocumentsUseCase
{
    public function __construct(
        private PropertyDocumentRepositoryInterface $documentRepository,
        private PropertyRepositoryInterface $propertyRepository,
    ) {}

    public function execute(string $propertyId, int $page = 1, int $perPage = 20): PaginatedResponseDto
    {
        $uuid = Uuid::fromString($propertyId);

        if ($this->propertyRepository->findById($uuid) === null) {
            throw new PropertyNotFoundException;
        }

        $result = $this->documentRepository->findByPropertyId($uuid, $page, $perPage);

        return new PaginatedResponseDto(
            items: array_map(
                fn ($entity) => PropertyDocumentResponseDto::fromEntity($entity),
                $result['items']
            ),
            total: $result['total'],
            page: $result['page'],
            perPage: $result['perPage'],
            lastPage: $result['lastPage'],
        );
    }
}
