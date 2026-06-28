<?php

declare(strict_types=1);

namespace Urbania\Propiedades\Application\UseCases\Condominiums;

use Urbania\Propiedades\Application\DTOs\CondominiumResponseDto;
use Urbania\Propiedades\Domain\Exceptions\CondominiumNotFoundException;
use Urbania\Propiedades\Domain\Repositories\CondominiumRepositoryInterface;
use Urbania\Shared\Domain\ValueObjects\Uuid;

final readonly class GetCondominiumUseCase
{
    public function __construct(
        private CondominiumRepositoryInterface $repository,
    ) {}

    public function execute(string $id): CondominiumResponseDto
    {
        $entity = $this->repository->findById(Uuid::fromString($id));

        if ($entity === null) {
            throw new CondominiumNotFoundException;
        }

        return CondominiumResponseDto::fromEntity($entity);
    }
}
