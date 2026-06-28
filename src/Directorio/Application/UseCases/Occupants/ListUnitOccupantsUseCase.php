<?php

declare(strict_types=1);

namespace Directorio\Application\UseCases\Occupants;

use Directorio\Domain\Entities\PropertyOccupant;
use Directorio\Domain\Repositories\PropertyOccupantRepository;

readonly class ListUnitOccupantsUseCase
{
    public function __construct(
        private PropertyOccupantRepository $occupantRepository,
    ) {}

    /** @return PropertyOccupant[] */
    public function execute(string $propertyId): array
    {
        return $this->occupantRepository->findByProperty($propertyId);
    }
}
