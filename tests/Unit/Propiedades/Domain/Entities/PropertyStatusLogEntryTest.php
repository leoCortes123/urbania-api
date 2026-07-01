<?php

declare(strict_types=1);

namespace Tests\Unit\Propiedades\Domain\Entities;

use Urbania\Propiedades\Domain\Entities\PropertyStatusLogEntry;
use Urbania\Shared\Domain\ValueObjects\Uuid;

it('creates a log entry with fromStatusId null', function (): void {
    $propertyId = Uuid::v7();
    $toStatusId = Uuid::v7();
    $changedByUserId = Uuid::v7();

    $logEntry = PropertyStatusLogEntry::create(
        $propertyId,
        null,
        $toStatusId,
        $changedByUserId,
        'Estado inicial',
    );

    expect($logEntry->propertyId()->toString())->toBe($propertyId->toString())
        ->and($logEntry->fromStatusId())->toBeNull()
        ->and($logEntry->toStatusId()->toString())->toBe($toStatusId->toString())
        ->and($logEntry->changedByUserId()->toString())->toBe($changedByUserId->toString())
        ->and($logEntry->reason())->toBe('Estado inicial')
        ->and($logEntry->createdAt())->toBeInstanceOf(\DateTimeImmutable::class);
});

it('creates a log entry with fromStatusId', function (): void {
    $propertyId = Uuid::v7();
    $fromStatusId = Uuid::v7();
    $toStatusId = Uuid::v7();
    $changedByUserId = Uuid::v7();

    $logEntry = PropertyStatusLogEntry::create(
        $propertyId,
        $fromStatusId,
        $toStatusId,
        $changedByUserId,
        'Cambio de estado',
    );

    expect($logEntry->fromStatusId())->toBeInstanceOf(Uuid::class)
        ->and($logEntry->fromStatusId()->toString())->toBe($fromStatusId->toString())
        ->and($logEntry->toStatusId()->toString())->toBe($toStatusId->toString());
});

it('exposes all getters', function (): void {
    $propertyId = Uuid::v7();
    $toStatusId = Uuid::v7();
    $changedByUserId = Uuid::v7();

    $logEntry = PropertyStatusLogEntry::create(
        $propertyId,
        null,
        $toStatusId,
        $changedByUserId,
        'Razón',
    );

    expect($logEntry->id())->toBeInstanceOf(Uuid::class)
        ->and($logEntry->propertyId())->toBeInstanceOf(Uuid::class)
        ->and($logEntry->fromStatusId())->toBeNull()
        ->and($logEntry->toStatusId())->toBeInstanceOf(Uuid::class)
        ->and($logEntry->changedByUserId())->toBeInstanceOf(Uuid::class)
        ->and($logEntry->reason())->toBeString()
        ->and($logEntry->createdAt())->toBeInstanceOf(\DateTimeImmutable::class);
});
