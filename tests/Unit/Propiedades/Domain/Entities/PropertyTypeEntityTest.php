<?php

declare(strict_types=1);

namespace Tests\Unit\Propiedades\Domain\Entities;

use Urbania\Propiedades\Domain\Entities\PropertyTypeEntity;
use Urbania\Shared\Domain\ValueObjects\Uuid;

it('creates a property type with code and name', function (): void {
    $propertyType = PropertyTypeEntity::create('APTO', 'Apartamento');

    expect($propertyType->code())->toBe('APTO')
        ->and($propertyType->name())->toBe('Apartamento')
        ->and($propertyType->description())->toBeNull()
        ->and($propertyType->sortOrder())->toBe(0)
        ->and($propertyType->isActive())->toBeTrue();
});

it('returns true for isActive by default', function (): void {
    $propertyType = PropertyTypeEntity::create('CASA', 'Casa');

    expect($propertyType->isActive())->toBeTrue();
});

it('update modifies name, description and sortOrder', function (): void {
    $propertyType = PropertyTypeEntity::create('APTO', 'Apartamento');
    $previousUpdatedAt = $propertyType->updatedAt();

    usleep(1000);

    $propertyType->update('Apartamento remodelado', 'Con balcon', 3);

    expect($propertyType->name())->toBe('Apartamento remodelado')
        ->and($propertyType->description())->toBe('Con balcon')
        ->and($propertyType->sortOrder())->toBe(3)
        ->and($propertyType->updatedAt())->toBeGreaterThan($previousUpdatedAt);
});

it('updateCode changes the code', function (): void {
    $propertyType = PropertyTypeEntity::create('APTO', 'Apartamento');
    $previousUpdatedAt = $propertyType->updatedAt();

    usleep(1000);

    $propertyType->updateCode('APT');

    expect($propertyType->code())->toBe('APT')
        ->and($propertyType->updatedAt())->toBeGreaterThan($previousUpdatedAt);
});

it('deactivate sets isActive to false and updates updatedAt', function (): void {
    $propertyType = PropertyTypeEntity::create('CASA', 'Casa');
    $previousUpdatedAt = $propertyType->updatedAt();

    usleep(1000);

    $propertyType->deactivate();

    expect($propertyType->isActive())->toBeFalse()
        ->and($propertyType->updatedAt())->toBeGreaterThan($previousUpdatedAt);
});

it('exposes all getters', function (): void {
    $propertyType = PropertyTypeEntity::create('LOTE', 'Lote', 'Lote de terreno', 1);

    expect($propertyType->id())->toBeInstanceOf(Uuid::class)
        ->and($propertyType->code())->toBeString()
        ->and($propertyType->name())->toBeString()
        ->and($propertyType->description())->toBeString()
        ->and($propertyType->sortOrder())->toBeInt()
        ->and($propertyType->isActive())->toBeBool()
        ->and($propertyType->createdAt())->toBeInstanceOf(\DateTimeImmutable::class)
        ->and($propertyType->updatedAt())->toBeInstanceOf(\DateTimeImmutable::class);
});
