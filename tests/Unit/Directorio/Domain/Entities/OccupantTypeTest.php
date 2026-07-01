<?php

declare(strict_types=1);

namespace Tests\Unit\Directorio\Domain\Entities;

use Directorio\Domain\Entities\OccupantType;
use Directorio\Domain\ValueObjects\OccupantTypeCode;
use Ramsey\Uuid\Uuid;

function createOccupantType(array $overrides = []): OccupantType
{
    return new OccupantType(
        $overrides['id'] ?? Uuid::uuid7()->toString(),
        $overrides['code'] ?? new OccupantTypeCode('OWNER'),
        $overrides['name'] ?? 'Propietario',
        $overrides['sortOrder'] ?? 1,
        $overrides['isActive'] ?? true,
    );
}

it('creates an occupant type with all values', function (): void {
    $occupantType = createOccupantType([
        'code' => new OccupantTypeCode('TENANT'),
        'name' => 'Arrendatario',
        'sortOrder' => 2,
        'isActive' => false,
    ]);

    expect($occupantType->id())->toBeString()
        ->and($occupantType->code()->value())->toBe('TENANT')
        ->and($occupantType->name())->toBe('Arrendatario')
        ->and($occupantType->sortOrder())->toBe(2)
        ->and($occupantType->isActive())->toBeFalse();
});

it('returns true for isActive by default', function (): void {
    $occupantType = createOccupantType();

    expect($occupantType->isActive())->toBeTrue();
});

it('exposes all getters', function (): void {
    $id = Uuid::uuid7()->toString();
    $occupantType = createOccupantType([
        'id' => $id,
        'code' => new OccupantTypeCode('RESIDENT'),
        'name' => 'Residente',
        'sortOrder' => 3,
        'isActive' => true,
    ]);

    expect($occupantType->id())->toBe($id)
        ->and($occupantType->code()->value())->toBe('RESIDENT')
        ->and($occupantType->name())->toBe('Residente')
        ->and($occupantType->sortOrder())->toBe(3)
        ->and($occupantType->isActive())->toBeTrue();
});
