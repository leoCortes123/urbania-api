<?php

declare(strict_types=1);

namespace Urbania\Propiedades\Application\UseCases\Towers;

use Urbania\Propiedades\Application\DTOs\TowerResponseDto;
use Urbania\Propiedades\Domain\Exceptions\TowerNotFoundException;
use Urbania\Propiedades\Domain\Repositories\TowerRepositoryInterface;
use Urbania\Shared\Domain\ValueObjects\Uuid;

final readonly class GetTowerUseCase
{
    public function __construct(
        private TowerRepositoryInterface $repository,
    ) {}

    public function execute(string $id): TowerResponseDto
    {
        $entity = $this->repository->findById(Uuid::fromString($id));

        if ($entity === null) {
            throw new TowerNotFoundException;
        }

        return TowerResponseDto::fromEntity($entity);
    }
}
