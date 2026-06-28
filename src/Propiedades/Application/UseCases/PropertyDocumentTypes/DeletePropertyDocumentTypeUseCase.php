<?php

declare(strict_types=1);

namespace Urbania\Propiedades\Application\UseCases\PropertyDocumentTypes;

use Urbania\Propiedades\Domain\Exceptions\PropertyDocumentTypeInUseException;
use Urbania\Propiedades\Domain\Exceptions\PropertyDocumentTypeNotFoundException;
use Urbania\Propiedades\Domain\Repositories\PropertyDocumentTypeRepositoryInterface;
use Urbania\Shared\Domain\ValueObjects\Uuid;

final readonly class DeletePropertyDocumentTypeUseCase
{
    public function __construct(
        private PropertyDocumentTypeRepositoryInterface $repository,
    ) {}

    public function execute(string $id): void
    {
        $uuid = Uuid::fromString($id);
        $entity = $this->repository->findById($uuid);

        if ($entity === null) {
            throw new PropertyDocumentTypeNotFoundException;
        }

        if ($this->repository->hasActiveDocuments($uuid)) {
            throw new PropertyDocumentTypeInUseException;
        }

        $entity->deactivate();
        $this->repository->save($entity);
    }
}
