<?php

declare(strict_types=1);

namespace Urbania\Propiedades\Application\Services;

final readonly class GenerateFullDesignationService
{
    public function execute(?string $towerCode, string $unitNumber): string
    {
        $code = $towerCode ?? 'SIN-TORRE';

        return "{$code} - {$unitNumber}";
    }
}
