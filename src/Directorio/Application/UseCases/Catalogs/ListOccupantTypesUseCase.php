<?php

declare(strict_types=1);

namespace Directorio\Application\UseCases\Catalogs;

use Directorio\Domain\Entities\OccupantType;
use Directorio\Domain\Repositories\OccupantTypeRepository;

readonly class ListOccupantTypesUseCase
{
    public function __construct(
        private OccupantTypeRepository $occupantTypeRepository,
    ) {}

    /** @return OccupantType[] */
    public function execute(): array
    {
        return $this->occupantTypeRepository->findAll();
    }
}
