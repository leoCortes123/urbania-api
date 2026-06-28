<?php

declare(strict_types=1);

namespace Urbania\Propiedades\Application\UseCases\Condominiums;

use Urbania\Propiedades\Application\DTOs\CondominiumResponseDto;
use Urbania\Propiedades\Application\DTOs\UpdateCondominiumRequestDto;
use Urbania\Propiedades\Domain\Exceptions\CondominiumNotFoundException;
use Urbania\Propiedades\Domain\Repositories\CondominiumRepositoryInterface;
use Urbania\Shared\Domain\ValueObjects\Uuid;

final readonly class UpdateCondominiumUseCase
{
    public function __construct(
        private CondominiumRepositoryInterface $repository,
    ) {}

    public function execute(string $id, UpdateCondominiumRequestDto $request): CondominiumResponseDto
    {
        $uuid = Uuid::fromString($id);
        $entity = $this->repository->findById($uuid);

        if ($entity === null) {
            throw new CondominiumNotFoundException;
        }

        $entity->update(
            name: $request->name,
            address: $request->address,
            city: $request->city,
            department: $request->department,
            country: $request->country,
            nit: $request->nit,
            phone: $request->phone,
            email: $request->email,
            legalRepresentative: $request->legalRepresentative,
            logoUrl: $request->logoUrl,
        );

        $this->repository->save($entity);

        return CondominiumResponseDto::fromEntity($entity);
    }
}
