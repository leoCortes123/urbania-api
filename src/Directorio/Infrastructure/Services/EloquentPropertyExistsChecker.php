<?php

declare(strict_types=1);

namespace Directorio\Infrastructure\Services;

use App\Models\Property;
use Directorio\Application\Services\PropertyExistsCheckerInterface;

final class EloquentPropertyExistsChecker implements PropertyExistsCheckerInterface
{
    public function exists(string $propertyId): bool
    {
        return Property::where('id', $propertyId)->exists();
    }
}
