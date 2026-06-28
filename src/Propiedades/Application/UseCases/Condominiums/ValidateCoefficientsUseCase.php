<?php

declare(strict_types=1);

namespace Urbania\Propiedades\Application\UseCases\Condominiums;

use Urbania\Propiedades\Domain\Exceptions\CondominiumNotFoundException;
use Urbania\Propiedades\Domain\Repositories\CondominiumRepositoryInterface;
use Urbania\Propiedades\Domain\Repositories\PropertyRepositoryInterface;
use Urbania\Shared\Domain\ValueObjects\Uuid;

final readonly class ValidateCoefficientsUseCase
{
    public function __construct(
        private CondominiumRepositoryInterface $condominiumRepository,
        private PropertyRepositoryInterface $propertyRepository,
    ) {}

    /**
     * @return array<string, mixed>
     */
    public function execute(string $id): array
    {
        $uuid = Uuid::fromString($id);
        $condominium = $this->condominiumRepository->findById($uuid);

        if ($condominium === null) {
            throw new CondominiumNotFoundException;
        }

        $totalExpected = (float) $condominium->totalCoefficient();
        $totalActual = $this->propertyRepository->sumCoefficientsByCondominium($uuid);
        $difference = round($totalActual - $totalExpected, 6);
        $unitCount = $this->propertyRepository->countByCondominium($uuid);

        return [
            'total_expected' => $totalExpected,
            'total_actual' => $totalActual,
            'difference' => $difference,
            'is_balanced' => abs($difference) < 0.000001,
            'unit_count' => $unitCount,
        ];
    }
}
