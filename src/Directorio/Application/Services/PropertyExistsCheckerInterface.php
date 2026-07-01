<?php

declare(strict_types=1);

namespace Directorio\Application\Services;

interface PropertyExistsCheckerInterface
{
    public function exists(string $propertyId): bool;
}
