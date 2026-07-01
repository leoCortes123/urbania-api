<?php

declare(strict_types=1);

namespace Tests\Unit\Propiedades\Domain\Entities;

use Urbania\Propiedades\Domain\Entities\PropertyStatusEntity;
use Urbania\Shared\Domain\ValueObjects\Uuid;

it('creates a property status with allowsResidents true by default', function (): void {
    $propertyStatus = PropertyStatusEntity::create('ACTIVO', 'Activo');

    expect($propertyStatus->code())->toBe('ACTIVO')
        ->and($propertyStatus->name())->toBe('Activo')
        ->and($propertyStatus->description())->toBeNull()
        ->and($propertyStatus->allowsResidents())->toBeTrue()
        ->and($propertyStatus->isActive())->toBeTrue()
        ->and($propertyStatus->sortOrder())->toBe(0);
});

it('creates a property status with allowsResidents false', function (): void {
    $propertyStatus = PropertyStatusEntity::create('EN_OBRA', 'En obra', false, 'Unidad en construcción', 5);

    expect($propertyStatus->allowsResidents())->toBeFalse()
        ->and($propertyStatus->description())->toBe('Unidad en construcción')
        ->and($propertyStatus->sortOrder())->toBe(5);
});

it('update modifies fields', function (): void {
    $propertyStatus = PropertyStatusEntity::create('ACTIVO', 'Activo');
    $previousUpdatedAt = $propertyStatus->updatedAt();

    usleep(1000);

    $propertyStatus->update('Activo habitado', 'Con residentes', true, 2);

    expect($propertyStatus->name())->toBe('Activo habitado')
        ->and($propertyStatus->description())->toBe('Con residentes')
        ->and($propertyStatus->allowsResidents())->toBeTrue()
        ->and($propertyStatus->sortOrder())->toBe(2)
        ->and($propertyStatus->updatedAt())->toBeGreaterThan($previousUpdatedAt);
});

it('updateCode changes the code', function (): void {
    $propertyStatus = PropertyStatusEntity::create('ACTIVO', 'Activo');
    $previousUpdatedAt = $propertyStatus->updatedAt();

    usleep(1000);

    $propertyStatus->updateCode('ACT');

    expect($propertyStatus->code())->toBe('ACT')
        ->and($propertyStatus->updatedAt())->toBeGreaterThan($previousUpdatedAt);
});

it('deactivate sets isActive to false', function (): void {
    $propertyStatus = PropertyStatusEntity::create('ACTIVO', 'Activo');
    $previousUpdatedAt = $propertyStatus->updatedAt();

    usleep(1000);

    $propertyStatus->deactivate();

    expect($propertyStatus->isActive())->toBeFalse()
        ->and($propertyStatus->updatedAt())->toBeGreaterThan($previousUpdatedAt);
});

it('exposes all getters', function (): void {
    $propertyStatus = PropertyStatusEntity::create('INACTIVO', 'Inactivo', false, 'Descripción', 1);

    expect($propertyStatus->id())->toBeInstanceOf(Uuid::class)
        ->and($propertyStatus->code())->toBeString()
        ->and($propertyStatus->name())->toBeString()
        ->and($propertyStatus->description())->toBeString()
        ->and($propertyStatus->allowsResidents())->toBeBool()
        ->and($propertyStatus->isActive())->toBeBool()
        ->and($propertyStatus->sortOrder())->toBeInt()
        ->and($propertyStatus->createdAt())->toBeInstanceOf(\DateTimeImmutable::class)
        ->and($propertyStatus->updatedAt())->toBeInstanceOf(\DateTimeImmutable::class);
});
