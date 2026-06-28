<?php

declare(strict_types=1);

namespace Urbania\Propiedades\Application\UseCases\PropertyDocuments;

use Urbania\Propiedades\Domain\Exceptions\PropertyDocumentNotFoundException;
use Urbania\Propiedades\Domain\Exceptions\PropertyNotFoundException;
use Urbania\Propiedades\Domain\Repositories\PropertyDocumentRepositoryInterface;
use Urbania\Propiedades\Domain\Repositories\PropertyRepositoryInterface;
use Urbania\Shared\Domain\ValueObjects\Uuid;

final readonly class DeletePropertyDocumentUseCase
{
    public function __construct(
        private PropertyDocumentRepositoryInterface $documentRepository,
        private PropertyRepositoryInterface $propertyRepository,
    ) {}

    public function execute(string $propertyId, string $documentId): void
    {
        $propertyUuid = Uuid::fromString($propertyId);
        $documentUuid = Uuid::fromString($documentId);

        if ($this->propertyRepository->findById($propertyUuid) === null) {
            throw new PropertyNotFoundException;
        }

        $document = $this->documentRepository->findById($documentUuid);

        if ($document === null || $document->propertyId()->toString() !== $propertyUuid->toString()) {
            throw new PropertyDocumentNotFoundException;
        }

        $this->documentRepository->delete($documentUuid);
    }
}
