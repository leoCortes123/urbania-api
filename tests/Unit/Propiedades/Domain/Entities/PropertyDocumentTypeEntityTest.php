<?php

declare(strict_types=1);

namespace Tests\Unit\Propiedades\Domain\Entities;

use Urbania\Propiedades\Domain\Entities\PropertyDocumentTypeEntity;
use Urbania\Shared\Domain\ValueObjects\Uuid;

it('creates a property document type with code and name', function (): void {
    $documentType = PropertyDocumentTypeEntity::create('CONTRATO', 'Contrato de compraventa');

    expect($documentType->code())->toBe('CONTRATO')
        ->and($documentType->name())->toBe('Contrato de compraventa')
        ->and($documentType->description())->toBeNull()
        ->and($documentType->sortOrder())->toBe(0)
        ->and($documentType->isActive())->toBeTrue();
});

it('returns true for isActive by default', function (): void {
    $documentType = PropertyDocumentTypeEntity::create('ESCRITURA', 'Escritura');

    expect($documentType->isActive())->toBeTrue();
});

it('update modifies name, description and sortOrder', function (): void {
    $documentType = PropertyDocumentTypeEntity::create('CONTRATO', 'Contrato');
    $previousUpdatedAt = $documentType->updatedAt();

    usleep(1000);

    $documentType->update('Contrato actualizado', 'Descripción actualizada', 4);

    expect($documentType->name())->toBe('Contrato actualizado')
        ->and($documentType->description())->toBe('Descripción actualizada')
        ->and($documentType->sortOrder())->toBe(4)
        ->and($documentType->updatedAt())->toBeGreaterThan($previousUpdatedAt);
});

it('updateCode changes the code', function (): void {
    $documentType = PropertyDocumentTypeEntity::create('CONTRATO', 'Contrato');
    $previousUpdatedAt = $documentType->updatedAt();

    usleep(1000);

    $documentType->updateCode('CONT');

    expect($documentType->code())->toBe('CONT')
        ->and($documentType->updatedAt())->toBeGreaterThan($previousUpdatedAt);
});

it('deactivate sets isActive to false', function (): void {
    $documentType = PropertyDocumentTypeEntity::create('CONTRATO', 'Contrato');
    $previousUpdatedAt = $documentType->updatedAt();

    usleep(1000);

    $documentType->deactivate();

    expect($documentType->isActive())->toBeFalse()
        ->and($documentType->updatedAt())->toBeGreaterThan($previousUpdatedAt);
});

it('exposes all getters', function (): void {
    $documentType = PropertyDocumentTypeEntity::create('ACTA', 'Acta de asamblea', 'Acta', 2);

    expect($documentType->id())->toBeInstanceOf(Uuid::class)
        ->and($documentType->code())->toBeString()
        ->and($documentType->name())->toBeString()
        ->and($documentType->description())->toBeString()
        ->and($documentType->sortOrder())->toBeInt()
        ->and($documentType->isActive())->toBeBool()
        ->and($documentType->createdAt())->toBeInstanceOf(\DateTimeImmutable::class)
        ->and($documentType->updatedAt())->toBeInstanceOf(\DateTimeImmutable::class);
});
