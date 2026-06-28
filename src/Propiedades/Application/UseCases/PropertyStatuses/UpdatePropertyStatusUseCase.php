<?php

declare(strict_types=1);

namespace Urbania\Propiedades\Application\UseCases\PropertyStatuses;

use Urbania\Propiedades\Application\DTOs\PropertyStatusResponseDto;
use Urbania\Propiedades\Application\DTOs\UpdatePropertyStatusRequestDto;
use Urbania\Propiedades\Domain\Exceptions\PropertyStatusCodeAlreadyExistsException;
use Urbania\Propiedades\Domain\Exceptions\PropertyStatusInUseException;
use Urbania\Propiedades\Domain\Exceptions\PropertyStatusNotFoundException;
use Urbania\Propiedades\Domain\Repositories\PropertyStatusRepositoryInterface;
use Urbania\Shared\Domain\ValueObjects\Uuid;

final readonly class UpdatePropertyStatusUseCase
{
    public function __construct(
        private PropertyStatusRepositoryInterface $repository,
    ) {}

    public function execute(string $id, UpdatePropertyStatusRequestDto $request): PropertyStatusResponseDto
    {
        $uuid = Uuid::fromString($id);
        $entity = $this->repository->findById($uuid);

        if ($entity === null) {
            throw new PropertyStatusNotFoundException;
        }

        if ($request->code !== null && $request->code !== $entity->code()) {
            if ($this->repository->hasActiveProperties($uuid)) {
                throw new PropertyStatusInUseException('No se puede cambiar el código porque el estado está en uso');
            }

            if ($this->repository->existsByCode($request->code, $uuid)) {
                throw new PropertyStatusCodeAlreadyExistsException;
            }

            $entity->updateCode($request->code);
        }

        $entity->update(
            name: $request->name ?? $entity->name(),
            description: $request->description !== null ? $request->description : $entity->description(),
            allowsResidents: $request->allowsResidents ?? $entity->allowsResidents(),
            sortOrder: $request->sortOrder ?? $entity->sortOrder(),
        );

        $this->repository->save($entity);

        return PropertyStatusResponseDto::fromEntity($entity);
    }
}
