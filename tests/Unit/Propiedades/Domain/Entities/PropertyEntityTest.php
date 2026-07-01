<?php

declare(strict_types=1);

namespace Tests\Unit\Propiedades\Domain\Entities;

use Urbania\Propiedades\Domain\Entities\PropertyEntity;
use Urbania\Shared\Domain\ValueObjects\Uuid;

function createProperty(array $overrides = []): PropertyEntity
{
    return PropertyEntity::create(
        $overrides['condominiumId'] ?? Uuid::v7(),
        $overrides['towerId'] ?? Uuid::v7(),
        $overrides['propertyTypeId'] ?? Uuid::v7(),
        $overrides['propertyStatusId'] ?? Uuid::v7(),
        $overrides['floor'] ?? 3,
        $overrides['unitNumber'] ?? '301',
        $overrides['areaM2'] ?? '85.50',
        $overrides['coefficient'] ?? '0.085000',
        $overrides['bedrooms'] ?? null,
        $overrides['bathrooms'] ?? null,
        $overrides['hasParking'] ?? false,
        $overrides['parkingLot'] ?? null,
        $overrides['notes'] ?? null,
    );
}

it('creates a property with required values', function (): void {
    $condominiumId = Uuid::v7();
    $towerId = Uuid::v7();
    $propertyTypeId = Uuid::v7();
    $propertyStatusId = Uuid::v7();

    $property = createProperty([
        'condominiumId' => $condominiumId,
        'towerId' => $towerId,
        'propertyTypeId' => $propertyTypeId,
        'propertyStatusId' => $propertyStatusId,
    ]);

    expect($property->condominiumId()->toString())->toBe($condominiumId->toString())
        ->and($property->towerId()->toString())->toBe($towerId->toString())
        ->and($property->propertyTypeId()->toString())->toBe($propertyTypeId->toString())
        ->and($property->propertyStatusId()->toString())->toBe($propertyStatusId->toString())
        ->and($property->floor())->toBe(3)
        ->and($property->unitNumber())->toBe('301')
        ->and($property->areaM2())->toBe('85.50')
        ->and($property->coefficient())->toBe('0.085000')
        ->and($property->bedrooms())->toBeNull()
        ->and($property->bathrooms())->toBeNull()
        ->and($property->hasParking())->toBeFalse()
        ->and($property->parkingLot())->toBeNull()
        ->and($property->notes())->toBeNull()
        ->and($property->deletedAt())->toBeNull();
});

it('creates a property with all optional fields', function (): void {
    $property = createProperty([
        'bedrooms' => 3,
        'bathrooms' => 2,
        'hasParking' => true,
        'parkingLot' => 'P-12',
        'notes' => 'Apartamento con vista',
    ]);

    expect($property->bedrooms())->toBe(3)
        ->and($property->bathrooms())->toBe(2)
        ->and($property->hasParking())->toBeTrue()
        ->and($property->parkingLot())->toBe('P-12')
        ->and($property->notes())->toBe('Apartamento con vista');
});

it('update modifies fields and updates updatedAt', function (): void {
    $property = createProperty();
    $previousUpdatedAt = $property->updatedAt();
    $newTowerId = Uuid::v7();
    $newPropertyTypeId = Uuid::v7();

    usleep(1000);

    $property->update(
        towerId: $newTowerId,
        propertyTypeId: $newPropertyTypeId,
        floor: 5,
        unitNumber: '501',
        areaM2: '92.00',
        coefficient: '0.092000',
        bedrooms: 2,
        bathrooms: 2,
        hasParking: true,
        parkingLot: 'P-05',
        notes: 'Nota actualizada',
    );

    expect($property->towerId()->toString())->toBe($newTowerId->toString())
        ->and($property->propertyTypeId()->toString())->toBe($newPropertyTypeId->toString())
        ->and($property->floor())->toBe(5)
        ->and($property->unitNumber())->toBe('501')
        ->and($property->areaM2())->toBe('92.00')
        ->and($property->coefficient())->toBe('0.092000')
        ->and($property->bedrooms())->toBe(2)
        ->and($property->bathrooms())->toBe(2)
        ->and($property->hasParking())->toBeTrue()
        ->and($property->parkingLot())->toBe('P-05')
        ->and($property->notes())->toBe('Nota actualizada')
        ->and($property->updatedAt())->toBeGreaterThan($previousUpdatedAt);
});

it('changeStatus changes propertyStatusId and updatedAt', function (): void {
    $property = createProperty();
    $previousUpdatedAt = $property->updatedAt();
    $newStatusId = Uuid::v7();

    usleep(1000);

    $property->changeStatus($newStatusId);

    expect($property->propertyStatusId()->toString())->toBe($newStatusId->toString())
        ->and($property->updatedAt())->toBeGreaterThan($previousUpdatedAt);
});

it('exposes all getters', function (): void {
    $property = createProperty([
        'bedrooms' => 3,
        'bathrooms' => 2,
        'parkingLot' => 'P-01',
        'notes' => 'Nota',
    ]);

    expect($property->id())->toBeInstanceOf(Uuid::class)
        ->and($property->condominiumId())->toBeInstanceOf(Uuid::class)
        ->and($property->towerId())->toBeInstanceOf(Uuid::class)
        ->and($property->propertyTypeId())->toBeInstanceOf(Uuid::class)
        ->and($property->propertyStatusId())->toBeInstanceOf(Uuid::class)
        ->and($property->floor())->toBeInt()
        ->and($property->unitNumber())->toBeString()
        ->and($property->areaM2())->toBeString()
        ->and($property->coefficient())->toBeString()
        ->and($property->bedrooms())->toBeInt()
        ->and($property->bathrooms())->toBeInt()
        ->and($property->hasParking())->toBeBool()
        ->and($property->parkingLot())->toBeString()
        ->and($property->notes())->toBeString()
        ->and($property->createdAt())->toBeInstanceOf(\DateTimeImmutable::class)
        ->and($property->updatedAt())->toBeInstanceOf(\DateTimeImmutable::class)
        ->and($property->deletedAt())->toBeNull();
});
