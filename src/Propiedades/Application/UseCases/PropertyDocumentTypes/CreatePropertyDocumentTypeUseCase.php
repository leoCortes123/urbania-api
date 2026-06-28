<?php

declare(strict_types=1);

namespace Urbania\Propiedades\Application\UseCases\PropertyDocumentTypes;

use Urbania\Propiedades\Application\DTOs\CreatePropertyDocumentTypeRequestDto;
use Urbania\Propiedades\Application\DTOs\PropertyDocumentTypeResponseDto;
use Urbania\Propiedades\Domain\Entities\PropertyDocumentTypeEntity;
use Urbania\Propiedades\Domain\Exceptions\PropertyDocumentTypeCodeAlreadyExistsException;
use Urbania\Propiedades\Domain\Repositories\PropertyDocumentTypeRepositoryInterface;

final readonly class CreatePropertyDocumentTypeUseCase
{
    public function __construct(
        private PropertyDocumentTypeRepositoryInterface $repository,
    ) {}

    public function execute(CreatePropertyDocumentTypeRequestDto $request): PropertyDocumentTypeResponseDto
    {
        if ($this->repository->existsByCode($request->code)) {
            throw new PropertyDocumentTypeCodeAlreadyExistsException;
        }

        $entity = PropertyDocumentTypeEntity::create(
            code: $request->code,
            name: $request->name,
            description: $request->description,
            sortOrder: $request->sortOrder,
        );

        $this->repository->save($entity);

        return PropertyDocumentTypeResponseDto::fromEntity($entity);
    }
}
