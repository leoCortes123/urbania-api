<?php

declare(strict_types=1);

namespace Urbania\Propiedades\Application\UseCases\Properties;

use Urbania\Propiedades\Application\DTOs\CreatePropertyRequestDto;
use Urbania\Propiedades\Application\DTOs\PropertyResponseDto;
use Urbania\Propiedades\Domain\Entities\PropertyEntity;
use Urbania\Propiedades\Domain\Entities\PropertyStatusLogEntry;
use Urbania\Propiedades\Domain\Exceptions\FloorExceedsTowerLimitException;
use Urbania\Propiedades\Domain\Exceptions\PropertyDuplicateUnitException;
use Urbania\Propiedades\Domain\Exceptions\PropertyStatusNotFoundException;
use Urbania\Propiedades\Domain\Exceptions\PropertyTypeNotFoundException;
use Urbania\Propiedades\Domain\Exceptions\TowerNotFoundException;
use Urbania\Propiedades\Domain\Repositories\PropertyRepositoryInterface;
use Urbania\Propiedades\Domain\Repositories\PropertyStatusLogRepositoryInterface;
use Urbania\Propiedades\Domain\Repositories\PropertyStatusRepositoryInterface;
use Urbania\Propiedades\Domain\Repositories\PropertyTypeRepositoryInterface;
use Urbania\Propiedades\Domain\Repositories\TowerRepositoryInterface;
use Urbania\Shared\Domain\ValueObjects\Uuid;

final readonly class CreatePropertyUseCase
{
    public function __construct(
        private PropertyRepositoryInterface $propertyRepository,
        private TowerRepositoryInterface $towerRepository,
        private PropertyTypeRepositoryInterface $propertyTypeRepository,
        private PropertyStatusRepositoryInterface $propertyStatusRepository,
        private PropertyStatusLogRepositoryInterface $statusLogRepository,
    ) {}

    public function execute(CreatePropertyRequestDto $request, string $changedByUserId): PropertyResponseDto
    {
        $tower = $this->towerRepository->findById($request->towerId);

        if ($tower === null) {
            throw new TowerNotFoundException;
        }

        if ($request->floor > $tower->floorCount()) {
            throw new FloorExceedsTowerLimitException;
        }

        $propertyType = $this->propertyTypeRepository->findById($request->propertyTypeId);

        if ($propertyType === null || ! $propertyType->isActive()) {
            throw new PropertyTypeNotFoundException;
        }

        $statusId = $request->propertyStatusId;
        if ($statusId === null) {
            $defaultStatus = $this->propertyStatusRepository->findByCode('vacia');
            if ($defaultStatus === null) {
                throw new PropertyStatusNotFoundException('Estado por defecto no encontrado');
            }
            $statusId = $defaultStatus->id();
        }

        $status = $this->propertyStatusRepository->findById($statusId);
        if ($status === null || ! $status->isActive()) {
            throw new PropertyStatusNotFoundException;
        }

        if ($this->propertyRepository->existsByUnitNumber($request->towerId, $request->floor, $request->unitNumber)) {
            throw new PropertyDuplicateUnitException;
        }

        $entity = PropertyEntity::create(
            condominiumId: $tower->condominiumId(),
            towerId: $request->towerId,
            propertyTypeId: $request->propertyTypeId,
            propertyStatusId: $statusId,
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

        $logEntry = PropertyStatusLogEntry::create(
            propertyId: $entity->id(),
            fromStatusId: null,
            toStatusId: $statusId,
            changedByUserId: Uuid::fromString($changedByUserId),
            reason: 'Creación de la unidad',
        );
        $this->statusLogRepository->save($logEntry);

        return PropertyResponseDto::fromEntity($entity);
    }
}
