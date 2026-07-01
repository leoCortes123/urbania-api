<?php

declare(strict_types=1);

namespace Tests\Unit\Propiedades\Domain\Entities;

use Urbania\Propiedades\Domain\Entities\TowerEntity;
use Urbania\Shared\Domain\ValueObjects\Uuid;

function createTower(array $overrides = []): TowerEntity
{
    return TowerEntity::create(
        $overrides['condominiumId'] ?? Uuid::v7(),
        $overrides['name'] ?? 'Torre A',
        $overrides['code'] ?? null,
        $overrides['floorCount'] ?? 1,
        $overrides['hasElevator'] ?? false,
        $overrides['description'] ?? null,
        $overrides['sortOrder'] ?? 0,
    );
}

it('creates a tower with default values', function (): void {
    $condominiumId = Uuid::v7();
    $tower = createTower(['condominiumId' => $condominiumId]);

    expect($tower->condominiumId()->toString())->toBe($condominiumId->toString())
        ->and($tower->name())->toBe('Torre A')
        ->and($tower->code())->toBeNull()
        ->and($tower->floorCount())->toBe(1)
        ->and($tower->hasElevator())->toBeFalse()
        ->and($tower->description())->toBeNull()
        ->and($tower->sortOrder())->toBe(0)
        ->and($tower->deletedAt())->toBeNull();
});

it('creates a tower with all fields', function (): void {
    $condominiumId = Uuid::v7();
    $tower = createTower([
        'condominiumId' => $condominiumId,
        'name' => 'Torre B',
        'code' => 'TB',
        'floorCount' => 12,
        'hasElevator' => true,
        'description' => 'Torre con ascensor',
        'sortOrder' => 2,
    ]);

    expect($tower->condominiumId()->toString())->toBe($condominiumId->toString())
        ->and($tower->name())->toBe('Torre B')
        ->and($tower->code())->toBe('TB')
        ->and($tower->floorCount())->toBe(12)
        ->and($tower->hasElevator())->toBeTrue()
        ->and($tower->description())->toBe('Torre con ascensor')
        ->and($tower->sortOrder())->toBe(2);
});

it('update modifies fields and updates updatedAt', function (): void {
    $tower = createTower();
    $previousUpdatedAt = $tower->updatedAt();

    usleep(1000);

    $tower->update(
        name: 'Torre C',
        code: 'TC',
        floorCount: 8,
        hasElevator: true,
        description: 'Nueva descripción',
        sortOrder: 5,
    );

    expect($tower->name())->toBe('Torre C')
        ->and($tower->code())->toBe('TC')
        ->and($tower->floorCount())->toBe(8)
        ->and($tower->hasElevator())->toBeTrue()
        ->and($tower->description())->toBe('Nueva descripción')
        ->and($tower->sortOrder())->toBe(5)
        ->and($tower->updatedAt())->toBeGreaterThan($previousUpdatedAt);
});

it('exposes all getters', function (): void {
    $tower = createTower([
        'code' => 'TA',
        'description' => 'Descripción',
    ]);

    expect($tower->id())->toBeInstanceOf(Uuid::class)
        ->and($tower->condominiumId())->toBeInstanceOf(Uuid::class)
        ->and($tower->name())->toBeString()
        ->and($tower->code())->toBeString()
        ->and($tower->floorCount())->toBeInt()
        ->and($tower->hasElevator())->toBeBool()
        ->and($tower->description())->toBeString()
        ->and($tower->sortOrder())->toBeInt()
        ->and($tower->createdAt())->toBeInstanceOf(\DateTimeImmutable::class)
        ->and($tower->updatedAt())->toBeInstanceOf(\DateTimeImmutable::class)
        ->and($tower->deletedAt())->toBeNull();
});
