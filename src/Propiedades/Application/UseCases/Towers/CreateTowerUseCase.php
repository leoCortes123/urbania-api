<?php

declare(strict_types=1);

namespace Urbania\Propiedades\Application\UseCases\Towers;

use Urbania\Propiedades\Application\DTOs\CreateTowerRequestDto;
use Urbania\Propiedades\Application\DTOs\TowerResponseDto;
use Urbania\Propiedades\Domain\Entities\TowerEntity;
use Urbania\Propiedades\Domain\Exceptions\CondominiumNotFoundException;
use Urbania\Propiedades\Domain\Exceptions\TowerNameAlreadyExistsException;
use Urbania\Propiedades\Domain\Repositories\CondominiumRepositoryInterface;
use Urbania\Propiedades\Domain\Repositories\TowerRepositoryInterface;

final readonly class CreateTowerUseCase
{
    public function __construct(
        private TowerRepositoryInterface $towerRepository,
        private CondominiumRepositoryInterface $condominiumRepository,
    ) {}

    public function execute(CreateTowerRequestDto $request): TowerResponseDto
    {
        if ($this->condominiumRepository->findById($request->condominiumId) === null) {
            throw new CondominiumNotFoundException;
        }

        if ($this->towerRepository->existsByNameInCondominium($request->name, $request->condominiumId)) {
            throw new TowerNameAlreadyExistsException;
        }

        $entity = TowerEntity::create(
            condominiumId: $request->condominiumId,
            name: $request->name,
            code: $request->code,
            floorCount: $request->floorCount,
            hasElevator: $request->hasElevator,
            description: $request->description,
            sortOrder: $request->sortOrder,
        );

        $this->towerRepository->save($entity);

        return TowerResponseDto::fromEntity($entity);
    }
}
