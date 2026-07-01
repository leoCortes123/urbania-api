<?php

declare(strict_types=1);

namespace Tests\Unit\Comunicaciones\Domain\Entities;

use Urbania\Comunicaciones\Domain\Entities\SurveyOptionEntity;
use Urbania\Shared\Domain\ValueObjects\Uuid;

function createSurveyOption(array $overrides = []): SurveyOptionEntity
{
    return SurveyOptionEntity::create(
        $overrides['surveyId'] ?? Uuid::v7(),
        $overrides['texto'] ?? 'Opción A',
        $overrides['orden'] ?? 0,
    );
}

it('create() creates option with orden=0 by default', function (): void {
    $surveyId = Uuid::v7();
    $option = createSurveyOption([
        'surveyId' => $surveyId,
    ]);

    expect($option->id())->toBeInstanceOf(Uuid::class)
        ->and($option->surveyId()->toString())->toBe($surveyId->toString())
        ->and($option->texto())->toBe('Opción A')
        ->and($option->orden())->toBe(0)
        ->and($option->createdAt())->toBeInstanceOf(\DateTimeImmutable::class)
        ->and($option->updatedAt())->toBeInstanceOf(\DateTimeImmutable::class);
});

it('reconstitute() restores entity with exact values', function (): void {
    $id = Uuid::v7();
    $surveyId = Uuid::v7();
    $createdAt = new \DateTimeImmutable('-1 day');
    $updatedAt = new \DateTimeImmutable('-1 hour');

    $option = SurveyOptionEntity::reconstitute(
        $id,
        $surveyId,
        'Opción B',
        5,
        $createdAt,
        $updatedAt,
    );

    expect($option->id()->toString())->toBe($id->toString())
        ->and($option->surveyId()->toString())->toBe($surveyId->toString())
        ->and($option->texto())->toBe('Opción B')
        ->and($option->orden())->toBe(5)
        ->and($option->createdAt())->toBe($createdAt)
        ->and($option->updatedAt())->toBe($updatedAt);
});

it('exposes all getters', function (): void {
    $option = createSurveyOption(['orden' => 3]);

    expect($option->id())->toBeInstanceOf(Uuid::class)
        ->and($option->surveyId())->toBeInstanceOf(Uuid::class)
        ->and($option->texto())->toBeString()
        ->and($option->orden())->toBeInt()
        ->and($option->createdAt())->toBeInstanceOf(\DateTimeImmutable::class)
        ->and($option->updatedAt())->toBeInstanceOf(\DateTimeImmutable::class);
});
