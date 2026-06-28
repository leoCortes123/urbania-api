<?php

declare(strict_types=1);

namespace Urbania\Propiedades\Application\UseCases\Properties;

use Urbania\Propiedades\Application\DTOs\PropertyResponseDto;
use Urbania\Propiedades\Application\DTOs\UpdatePropertyRequestDto;
use Urbania\Propiedades\Domain\Exceptions\FloorExceedsTowerLimitException;
use Urbania\Propiedades\Domain\Exceptions\PropertyDuplicateUnitException;
use Urbania\Propiedades\Domain\Exceptions\PropertyNotFoundException;
use Urbania\Propiedades\Domain\Exceptions\PropertyTypeNotFoundException;
use Urbania\Propiedades\Domain\Exceptions\TowerNotFoundException;
use Urbania\Propiedades\Domain\Repositories\PropertyRepositoryInterface;
use Urbania\Propiedades\Domain\Repositories\PropertyTypeRepositoryInterface;
use Urbania\Propiedades\Domain\Repositories\TowerRepositoryInterface;
use Urbania\Shared\Domain\ValueObjects\Uuid;

final readonly class UpdatePropertyUseCase
{
    public function __construct(
        private PropertyRepositoryInterface $propertyRepository,
        private TowerRepositoryInterface $towerRepository,
        private PropertyTypeRepositoryInterface $propertyTypeRepository,
    ) {}

    public function execute(string $id, UpdatePropertyRequestDto $request): PropertyResponseDto
    {
        $uuid = Uuid::fromString($id);
        $entity = $this->propertyRepository->findById($uuid);

        if ($entity === null) {
            throw new PropertyNotFoundException;
        }

        $towerId = $request->towerId ?? $entity->towerId();
        $tower = $this->towerRepository->findById($towerId);
        if ($tower === null) {
            throw new TowerNotFoundException;
        }

        $floor = $request->floor ?? $entity->floor();
        $unitNumber = $request->unitNumber ?? $entity->unitNumber();

        $towerChanged = $towerId->toString() !== $entity->towerId()->toString();
        $floorChanged = $request->floor !== null && $floor !== $entity->floor();
        $unitNumberChanged = $request->unitNumber !== null && $unitNumber !== $entity->unitNumber();

        if ($towerChanged || $floorChanged) {
            if ($floor > $tower->floorCount()) {
                throw new FloorExceedsTowerLimitException;
            }
        }

        if ($towerChanged || $floorChanged || $unitNumberChanged) {
            if ($this->propertyRepository->existsByUnitNumber($towerId, $floor, $unitNumber, $uuid)) {
                throw new PropertyDuplicateUnitException;
            }
        }

        if ($request->propertyTypeId !== null) {
            $type = $this->propertyTypeRepository->findById($request->propertyTypeId);
            if ($type === null || ! $type->isActive()) {
                throw new PropertyTypeNotFoundException;
            }
        }

        $entity->update(
            towerId: $request->towerId,
            propertyTypeId: $request->propertyTypeId,
            floor: $request->floor,
            unitNumber: $request->unitNumber,
            areaM2: $request->areaM2,
            coefficient: $request->coefficient,
            bedrooms: $request->bedrooms,
            bathrooms: $request->bathrooms,
            hasParking: $request->hasParking,
            parkingLot: $request->parkingLot,
            notes: $request->notes,
        );

        $this->propertyRepository->save($entity);

        return PropertyResponseDto::fromEntity($entity);
    }
}
