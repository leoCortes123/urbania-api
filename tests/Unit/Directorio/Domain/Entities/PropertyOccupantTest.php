<?php

declare(strict_types=1);

namespace Tests\Unit\Directorio\Domain\Entities;

use Directorio\Domain\Entities\PropertyOccupant;
use InvalidArgumentException;
use Ramsey\Uuid\Uuid;

function createPropertyOccupant(array $overrides = []): PropertyOccupant
{
    return new PropertyOccupant(
        $overrides['id'] ?? Uuid::uuid7()->toString(),
        $overrides['propertyId'] ?? Uuid::uuid7()->toString(),
        $overrides['contactId'] ?? Uuid::uuid7()->toString(),
        $overrides['occupantTypeId'] ?? Uuid::uuid7()->toString(),
        $overrides['isPrimary'] ?? false,
        $overrides['moveInDate'] ?? null,
        $overrides['moveOutDate'] ?? null,
        $overrides['isActive'] ?? true,
        $overrides['createdAt'] ?? null,
        $overrides['updatedAt'] ?? null,
        $overrides['deletedAt'] ?? null,
    );
}

it('creates a property occupant with default values', function (): void {
    $occupant = createPropertyOccupant();

    expect($occupant->id())->toBeString()
        ->and($occupant->propertyId())->toBeString()
        ->and($occupant->contactId())->toBeString()
        ->and($occupant->occupantTypeId())->toBeString()
        ->and($occupant->isPrimary())->toBeFalse()
        ->and($occupant->moveInDate())->toBeNull()
        ->and($occupant->moveOutDate())->toBeNull()
        ->and($occupant->isActive())->toBeTrue()
        ->and($occupant->createdAt())->toBeNull()
        ->and($occupant->updatedAt())->toBeNull()
        ->and($occupant->deletedAt())->toBeNull()
        ->and($occupant->isDeleted())->toBeFalse()
        ->and($occupant->isCurrentlyOccupying())->toBeTrue();
});

it('creates a property occupant with move dates', function (): void {
    $occupant = createPropertyOccupant([
        'moveInDate' => '2026-01-15',
        'moveOutDate' => '2026-12-15',
    ]);

    expect($occupant->moveInDate())->toBe('2026-01-15')
        ->and($occupant->moveOutDate())->toBe('2026-12-15');
});

it('throws exception when move out date is before move in date', function (): void {
    createPropertyOccupant([
        'moveInDate' => '2026-12-15',
        'moveOutDate' => '2026-01-15',
    ]);
})->throws(InvalidArgumentException::class, 'La fecha de salida no puede ser anterior a la fecha de ingreso');

it('returns true for isCurrentlyOccupying when active and not deleted', function (): void {
    $occupant = createPropertyOccupant();

    expect($occupant->isCurrentlyOccupying())->toBeTrue();
});

it('returns false for isCurrentlyOccupying when inactive or deleted', function (): void {
    $inactive = createPropertyOccupant(['isActive' => false]);
    $deleted = createPropertyOccupant(['deletedAt' => '2026-06-30 00:00:00']);

    expect($inactive->isCurrentlyOccupying())->toBeFalse()
        ->and($deleted->isCurrentlyOccupying())->toBeFalse();
});

it('returns false for isDeleted by default', function (): void {
    $occupant = createPropertyOccupant();

    expect($occupant->isDeleted())->toBeFalse();
});
