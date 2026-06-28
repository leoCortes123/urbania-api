<?php

declare(strict_types=1);

namespace Urbania\Propiedades\Application\UseCases\PropertyTypes;

use Urbania\Propiedades\Application\DTOs\PropertyTypeResponseDto;
use Urbania\Propiedades\Application\DTOs\UpdatePropertyTypeRequestDto;
use Urbania\Propiedades\Domain\Exceptions\PropertyTypeCodeAlreadyExistsException;
use Urbania\Propiedades\Domain\Exceptions\PropertyTypeInUseException;
use Urbania\Propiedades\Domain\Exceptions\PropertyTypeNotFoundException;
use Urbania\Propiedades\Domain\Repositories\PropertyTypeRepositoryInterface;
use Urbania\Shared\Domain\ValueObjects\Uuid;

final readonly class UpdatePropertyTypeUseCase
{
    public function __construct(
        private PropertyTypeRepositoryInterface $repository,
    ) {}

    public function execute(string $id, UpdatePropertyTypeRequestDto $request): PropertyTypeResponseDto
    {
        $uuid = Uuid::fromString($id);
        $entity = $this->repository->findById($uuid);

        if ($entity === null) {
            throw new PropertyTypeNotFoundException;
        }

        if ($request->code !== null && $request->code !== $entity->code()) {
            if ($this->repository->hasActiveProperties($uuid)) {
                throw new PropertyTypeInUseException('No se puede cambiar el código porque el tipo está en uso');
            }

            if ($this->repository->existsByCode($request->code, $uuid)) {
                throw new PropertyTypeCodeAlreadyExistsException;
            }

            $entity->updateCode($request->code);
        }

        $entity->update(
            name: $request->name ?? $entity->name(),
            description: $request->description !== null ? $request->description : $entity->description(),
            sortOrder: $request->sortOrder ?? $entity->sortOrder(),
        );

        $this->repository->save($entity);

        return PropertyTypeResponseDto::fromEntity($entity);
    }
}
