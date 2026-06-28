<?php

declare(strict_types=1);

namespace Urbania\Propiedades\Application\UseCases\PropertyStatuses;

use Urbania\Propiedades\Application\DTOs\CreatePropertyStatusRequestDto;
use Urbania\Propiedades\Application\DTOs\PropertyStatusResponseDto;
use Urbania\Propiedades\Domain\Entities\PropertyStatusEntity;
use Urbania\Propiedades\Domain\Exceptions\PropertyStatusCodeAlreadyExistsException;
use Urbania\Propiedades\Domain\Repositories\PropertyStatusRepositoryInterface;

final readonly class CreatePropertyStatusUseCase
{
    public function __construct(
        private PropertyStatusRepositoryInterface $repository,
    ) {}

    public function execute(CreatePropertyStatusRequestDto $request): PropertyStatusResponseDto
    {
        if ($this->repository->existsByCode($request->code)) {
            throw new PropertyStatusCodeAlreadyExistsException;
        }

        $entity = PropertyStatusEntity::create(
            code: $request->code,
            name: $request->name,
            allowsResidents: $request->allowsResidents,
            description: $request->description,
            sortOrder: $request->sortOrder,
        );

        $this->repository->save($entity);

        return PropertyStatusResponseDto::fromEntity($entity);
    }
}
