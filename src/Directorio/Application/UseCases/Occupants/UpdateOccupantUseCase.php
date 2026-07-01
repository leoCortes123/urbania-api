<?php

declare(strict_types=1);

namespace Directorio\Application\UseCases\Occupants;

use Directorio\Application\DTOs\UpdateOccupantDTO;
use Directorio\Domain\Entities\PropertyOccupant;
use Directorio\Domain\Exceptions\OccupantNotFoundException;
use Directorio\Domain\Repositories\PropertyOccupantRepository;

readonly class UpdateOccupantUseCase
{
    public function __construct(
        private PropertyOccupantRepository $occupantRepository,
    ) {}

    public function execute(string $id, UpdateOccupantDTO $dto): PropertyOccupant
    {
        $occupant = $this->occupantRepository->findById($id);
        if ($occupant === null) {
            throw new OccupantNotFoundException;
        }

        $updated = new PropertyOccupant(
            id: $id,
            propertyId: $dto->occupantTypeId !== null ? $occupant->propertyId() : $occupant->propertyId(),
            contactId: $occupant->contactId(),
            occupantTypeId: $dto->occupantTypeId ?? $occupant->occupantTypeId(),
            isPrimary: $dto->isPrimary ?? $occupant->isPrimary(),
            moveInDate: $dto->moveInDate ?? $occupant->moveInDate(),
            moveOutDate: $dto->moveOutDate ?? $occupant->moveOutDate(),
            isActive: $dto->isActive ?? $occupant->isActive(),
        );

        return $this->occupantRepository->update($updated);
    }
}
