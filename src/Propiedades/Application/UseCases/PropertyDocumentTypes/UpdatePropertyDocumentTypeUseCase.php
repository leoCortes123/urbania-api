<?php

declare(strict_types=1);

namespace Urbania\Propiedades\Application\UseCases\PropertyDocumentTypes;

use Urbania\Propiedades\Application\DTOs\PropertyDocumentTypeResponseDto;
use Urbania\Propiedades\Application\DTOs\UpdatePropertyDocumentTypeRequestDto;
use Urbania\Propiedades\Domain\Exceptions\PropertyDocumentTypeCodeAlreadyExistsException;
use Urbania\Propiedades\Domain\Exceptions\PropertyDocumentTypeInUseException;
use Urbania\Propiedades\Domain\Exceptions\PropertyDocumentTypeNotFoundException;
use Urbania\Propiedades\Domain\Repositories\PropertyDocumentTypeRepositoryInterface;
use Urbania\Shared\Domain\ValueObjects\Uuid;

final readonly class UpdatePropertyDocumentTypeUseCase
{
    public function __construct(
        private PropertyDocumentTypeRepositoryInterface $repository,
    ) {}

    public function execute(string $id, UpdatePropertyDocumentTypeRequestDto $request): PropertyDocumentTypeResponseDto
    {
        $uuid = Uuid::fromString($id);
        $entity = $this->repository->findById($uuid);

        if ($entity === null) {
            throw new PropertyDocumentTypeNotFoundException;
        }

        if ($request->code !== null && $request->code !== $entity->code()) {
            if ($this->repository->hasActiveDocuments($uuid)) {
                throw new PropertyDocumentTypeInUseException('No se puede cambiar el código porque el tipo está en uso');
            }

            if ($this->repository->existsByCode($request->code, $uuid)) {
                throw new PropertyDocumentTypeCodeAlreadyExistsException;
            }

            $entity->updateCode($request->code);
        }

        $entity->update(
            name: $request->name ?? $entity->name(),
            description: $request->description !== null ? $request->description : $entity->description(),
            sortOrder: $request->sortOrder ?? $entity->sortOrder(),
        );

        $this->repository->save($entity);

        return PropertyDocumentTypeResponseDto::fromEntity($entity);
    }
}
