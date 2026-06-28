<?php

declare(strict_types=1);

namespace Urbania\Propiedades\Application\UseCases\Towers;

use Urbania\Propiedades\Domain\Exceptions\TowerHasPropertiesException;
use Urbania\Propiedades\Domain\Exceptions\TowerNotFoundException;
use Urbania\Propiedades\Domain\Repositories\PropertyRepositoryInterface;
use Urbania\Propiedades\Domain\Repositories\TowerRepositoryInterface;
use Urbania\Shared\Domain\ValueObjects\Uuid;

final readonly class DeleteTowerUseCase
{
    public function __construct(
        private TowerRepositoryInterface $towerRepository,
        private PropertyRepositoryInterface $propertyRepository,
    ) {}

    public function execute(string $id): void
    {
        $uuid = Uuid::fromString($id);
        $entity = $this->towerRepository->findById($uuid);

        if ($entity === null) {
            throw new TowerNotFoundException;
        }

        if ($this->propertyRepository->countByTower($uuid) > 0) {
            throw new TowerHasPropertiesException;
        }

        $this->towerRepository->delete($uuid);
    }
}
