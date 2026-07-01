<?php

declare(strict_types=1);

namespace Directorio\Application\UseCases\Occupants;

use Directorio\Application\Services\PropertyExistsCheckerInterface;
use Directorio\Domain\Entities\PropertyOccupant;
use Directorio\Domain\Exceptions\OccupantNotFoundException;
use Directorio\Domain\Repositories\PropertyOccupantRepository;

readonly class ListUnitOccupantsUseCase
{
    public function __construct(
        private PropertyOccupantRepository $occupantRepository,
        private PropertyExistsCheckerInterface $propertyExistsChecker,
    ) {}

    /** @return PropertyOccupant[] */
    public function execute(string $propertyId): array
    {
        if (! $this->propertyExistsChecker->exists($propertyId)) {
            throw new OccupantNotFoundException;
        }

        return $this->occupantRepository->findByProperty($propertyId);
    }
}
