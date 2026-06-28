<?php

declare(strict_types=1);

namespace Directorio\Application\UseCases\Occupants;

use Directorio\Application\DTOs\CreateOccupantDTO;
use Directorio\Domain\Entities\PropertyOccupant;
use Directorio\Domain\Exceptions\DuplicateOccupantException;
use Directorio\Domain\Repositories\OccupantTypeRepository;
use Directorio\Domain\Repositories\PropertyOccupantRepository;
use Ramsey\Uuid\Uuid;

readonly class LinkContactToUnitUseCase
{
    public function __construct(
        private PropertyOccupantRepository $occupantRepository,
        private OccupantTypeRepository $occupantTypeRepository,
    ) {}

    public function execute(string $propertyId, CreateOccupantDTO $dto): PropertyOccupant
    {
        // Validar que el tipo de ocupante exista
        $occupantType = $this->occupantTypeRepository->findById($dto->occupantTypeId);
        if ($occupantType === null) {
            throw new \InvalidArgumentException('Tipo de ocupante no encontrado');
        }

        // Validar que no exista duplicado
        $existing = $this->occupantRepository->findActiveByPropertyAndType($propertyId, $dto->occupantTypeId);
        foreach ($existing as $occ) {
            if ($occ->contactId() === $dto->contactId) {
                throw new DuplicateOccupantException;
            }
        }

        $occupant = new PropertyOccupant(
            id: Uuid::uuid7()->toString(),
            propertyId: $propertyId,
            contactId: $dto->contactId,
            occupantTypeId: $dto->occupantTypeId,
            isPrimary: $dto->isPrimary,
            moveInDate: $dto->moveInDate,
            moveOutDate: $dto->moveOutDate,
        );

        return $this->occupantRepository->save($occupant);
    }
}
