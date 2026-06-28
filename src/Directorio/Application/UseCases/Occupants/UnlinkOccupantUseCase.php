<?php

declare(strict_types=1);

namespace Directorio\Application\UseCases\Occupants;

use Directorio\Domain\Exceptions\MustHaveOwnerException;
use Directorio\Domain\Exceptions\OccupantNotFoundException;
use Directorio\Domain\Repositories\OccupantTypeRepository;
use Directorio\Domain\Repositories\PropertyOccupantRepository;

readonly class UnlinkOccupantUseCase
{
    public function __construct(
        private PropertyOccupantRepository $occupantRepository,
        private OccupantTypeRepository $occupantTypeRepository,
    ) {}

    public function execute(string $id): void
    {
        $occupant = $this->occupantRepository->findById($id);
        if ($occupant === null) {
            throw new OccupantNotFoundException($id);
        }

        // Si es propietario, validar que quede al menos otro propietario en la unidad
        $ownerType = $this->occupantTypeRepository->findByCode('propietario');
        if ($ownerType !== null && $occupant->occupantTypeId() === $ownerType->id()) {
            $activeOwners = $this->occupantRepository->countActiveOwnersByProperty($occupant->propertyId());
            if ($activeOwners <= 1) {
                throw new MustHaveOwnerException($occupant->propertyId());
            }
        }

        $this->occupantRepository->delete($id);
    }
}
