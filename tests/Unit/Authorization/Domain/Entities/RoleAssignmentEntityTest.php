<?php

declare(strict_types=1);

namespace Tests\Unit\Authorization\Domain\Entities;

use Urbania\Authorization\Domain\Entities\RoleAssignment;
use Urbania\Shared\Domain\ValueObjects\Uuid;

function createRoleAssignment(array $overrides = []): RoleAssignment
{
    return new RoleAssignment(
        id: $overrides['id'] ?? Uuid::v7(),
        userId: $overrides['userId'] ?? Uuid::v7(),
        roleId: $overrides['roleId'] ?? Uuid::v7(),
        scopeType: $overrides['scopeType'] ?? 'condominium',
        scopeId: $overrides['scopeId'] ?? Uuid::v7(),
        startsAt: $overrides['startsAt'] ?? null,
        endsAt: $overrides['endsAt'] ?? null,
    );
}

it('constructor assigns correct values', function (): void {
    $id = Uuid::v7();
    $userId = Uuid::v7();
    $roleId = Uuid::v7();
    $scopeId = Uuid::v7();
    $startsAt = new \DateTimeImmutable('2026-01-01');
    $endsAt = new \DateTimeImmutable('2026-12-31');

    $assignment = createRoleAssignment([
        'id' => $id,
        'userId' => $userId,
        'roleId' => $roleId,
        'scopeType' => 'organization',
        'scopeId' => $scopeId,
        'startsAt' => $startsAt,
        'endsAt' => $endsAt,
    ]);

    expect($assignment->id()->toString())->toBe($id->toString())
        ->and($assignment->userId()->toString())->toBe($userId->toString())
        ->and($assignment->roleId()->toString())->toBe($roleId->toString())
        ->and($assignment->scopeType())->toBe('organization')
        ->and($assignment->scopeId()->toString())->toBe($scopeId->toString())
        ->and($assignment->startsAt())->toEqual($startsAt)
        ->and($assignment->endsAt())->toEqual($endsAt);
});

it('isActive returns true without startsAt and endsAt', function (): void {
    $assignment = createRoleAssignment();

    expect($assignment->isActive())->toBeTrue();
});

it('isActive returns false when startsAt is in the future', function (): void {
    $future = new \DateTimeImmutable('+7 days');
    $assignment = createRoleAssignment(['startsAt' => $future]);

    expect($assignment->isActive())->toBeFalse();
});

it('isActive returns false when endsAt is in the past', function (): void {
    $past = new \DateTimeImmutable('-7 days');
    $assignment = createRoleAssignment(['endsAt' => $past]);

    expect($assignment->isActive())->toBeFalse();
});

it('isActive returns true when startsAt is in the past and endsAt is in the future', function (): void {
    $past = new \DateTimeImmutable('-7 days');
    $future = new \DateTimeImmutable('+7 days');

    $assignment = createRoleAssignment([
        'startsAt' => $past,
        'endsAt' => $future,
    ]);

    expect($assignment->isActive())->toBeTrue();
});

it('isActive returns true when startsAt is in the past and endsAt is null', function (): void {
    $past = new \DateTimeImmutable('-7 days');

    $assignment = createRoleAssignment([
        'startsAt' => $past,
        'endsAt' => null,
    ]);

    expect($assignment->isActive())->toBeTrue();
});
