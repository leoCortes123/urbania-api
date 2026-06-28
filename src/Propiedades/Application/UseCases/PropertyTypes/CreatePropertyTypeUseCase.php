<?php

declare(strict_types=1);

namespace Urbania\Propiedades\Application\UseCases\PropertyTypes;

use Urbania\Propiedades\Application\DTOs\CreatePropertyTypeRequestDto;
use Urbania\Propiedades\Application\DTOs\PropertyTypeResponseDto;
use Urbania\Propiedades\Domain\Entities\PropertyTypeEntity;
use Urbania\Propiedades\Domain\Exceptions\PropertyTypeCodeAlreadyExistsException;
use Urbania\Propiedades\Domain\Repositories\PropertyTypeRepositoryInterface;

final readonly class CreatePropertyTypeUseCase
{
    public function __construct(
        private PropertyTypeRepositoryInterface $repository,
    ) {}

    public function execute(CreatePropertyTypeRequestDto $request): PropertyTypeResponseDto
    {
        if ($this->repository->existsByCode($request->code)) {
            throw new PropertyTypeCodeAlreadyExistsException;
        }

        $entity = PropertyTypeEntity::create(
            code: $request->code,
            name: $request->name,
            description: $request->description,
            sortOrder: $request->sortOrder,
        );

        $this->repository->save($entity);

        return PropertyTypeResponseDto::fromEntity($entity);
    }
}
