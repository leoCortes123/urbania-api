<?php

declare(strict_types=1);

namespace Urbania\Propiedades\Application\UseCases\Towers;

use Urbania\Propiedades\Application\DTOs\TowerResponseDto;
use Urbania\Propiedades\Application\DTOs\UpdateTowerRequestDto;
use Urbania\Propiedades\Domain\Exceptions\FloorExceedsTowerLimitException;
use Urbania\Propiedades\Domain\Exceptions\TowerNameAlreadyExistsException;
use Urbania\Propiedades\Domain\Exceptions\TowerNotFoundException;
use Urbania\Propiedades\Domain\Repositories\PropertyRepositoryInterface;
use Urbania\Propiedades\Domain\Repositories\TowerRepositoryInterface;
use Urbania\Shared\Domain\ValueObjects\Uuid;

final readonly class UpdateTowerUseCase
{
    public function __construct(
        private TowerRepositoryInterface $towerRepository,
        private PropertyRepositoryInterface $propertyRepository,
    ) {}

    public function execute(string $id, UpdateTowerRequestDto $request): TowerResponseDto
    {
        $uuid = Uuid::fromString($id);
        $entity = $this->towerRepository->findById($uuid);

        if ($entity === null) {
            throw new TowerNotFoundException;
        }

        if ($request->name !== null && $request->name !== $entity->name()) {
            if ($this->towerRepository->existsByNameInCondominium($request->name, $entity->condominiumId(), $uuid)) {
                throw new TowerNameAlreadyExistsException;
            }
        }

        $newFloorCount = $request->floorCount ?? $entity->floorCount();
        if ($newFloorCount !== $entity->floorCount()) {
            $maxFloor = $this->propertyRepository->findByCondominiumAndTower($entity->condominiumId(), $uuid);
            $maxFloorValue = array_reduce(
                $maxFloor,
                fn (int $carry, $property): int => max($carry, $property->floor()),
                0
            );

            if ($maxFloorValue > $newFloorCount) {
                throw new FloorExceedsTowerLimitException('No se puede reducir la torre porque tiene unidades en pisos superiores');
            }
        }

        $entity->update(
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
