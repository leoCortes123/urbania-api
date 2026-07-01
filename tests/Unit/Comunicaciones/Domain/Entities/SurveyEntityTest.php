<?php

declare(strict_types=1);

namespace Tests\Unit\Comunicaciones\Domain\Entities;

use Urbania\Comunicaciones\Domain\Entities\SurveyEntity;
use Urbania\Comunicaciones\Domain\ValueObjects\SurveyType;
use Urbania\Shared\Domain\ValueObjects\Uuid;

function createSurvey(array $overrides = []): SurveyEntity
{
    return SurveyEntity::create(
        $overrides['condominiumId'] ?? Uuid::v7(),
        $overrides['pregunta'] ?? '¿Está satisfecho con el servicio?',
        $overrides['tipo'] ?? SurveyType::SIMPLE,
        $overrides['cierraEl'] ?? null,
        $overrides['activa'] ?? true,
    );
}

it('create() creates survey with activa=true by default', function (): void {
    $condominiumId = Uuid::v7();
    $survey = createSurvey([
        'condominiumId' => $condominiumId,
    ]);

    expect($survey->id())->toBeInstanceOf(Uuid::class)
        ->and($survey->condominiumId()->toString())->toBe($condominiumId->toString())
        ->and($survey->pregunta())->toBe('¿Está satisfecho con el servicio?')
        ->and($survey->tipo())->toBe(SurveyType::SIMPLE)
        ->and($survey->cierraEl())->toBeNull()
        ->and($survey->activa())->toBeTrue()
        ->and($survey->createdAt())->toBeInstanceOf(\DateTimeImmutable::class)
        ->and($survey->updatedAt())->toBeInstanceOf(\DateTimeImmutable::class);
});

it('reconstitute() restores entity with exact values', function (): void {
    $id = Uuid::v7();
    $condominiumId = Uuid::v7();
    $cierraEl = new \DateTimeImmutable('+5 days');
    $createdAt = new \DateTimeImmutable('-1 day');
    $updatedAt = new \DateTimeImmutable('-1 hour');

    $survey = SurveyEntity::reconstitute(
        $id,
        $condominiumId,
        '¿Le gustan las áreas comunes?',
        SurveyType::MULTIPLE,
        $cierraEl,
        true,
        $createdAt,
        $updatedAt,
    );

    expect($survey->id()->toString())->toBe($id->toString())
        ->and($survey->condominiumId()->toString())->toBe($condominiumId->toString())
        ->and($survey->pregunta())->toBe('¿Le gustan las áreas comunes?')
        ->and($survey->tipo())->toBe(SurveyType::MULTIPLE)
        ->and($survey->cierraEl())->toBe($cierraEl)
        ->and($survey->activa())->toBeTrue()
        ->and($survey->createdAt())->toBe($createdAt)
        ->and($survey->updatedAt())->toBe($updatedAt);
});

it('isClosed() returns false when activa and no cierraEl', function (): void {
    $survey = createSurvey([
        'activa' => true,
        'cierraEl' => null,
    ]);

    expect($survey->isClosed())->toBeFalse();
});

it('isClosed() returns true when inactiva regardless of cierraEl', function (): void {
    $survey = createSurvey([
        'activa' => false,
        'cierraEl' => null,
    ]);

    expect($survey->isClosed())->toBeTrue();
});

it('isClosed() returns true when cierraEl is in the past', function (): void {
    $survey = createSurvey([
        'activa' => true,
        'cierraEl' => new \DateTimeImmutable('-1 day'),
    ]);

    expect($survey->isClosed())->toBeTrue();
});

it('isClosed() returns false when cierraEl is in the future', function (): void {
    $survey = createSurvey([
        'activa' => true,
        'cierraEl' => new \DateTimeImmutable('+1 day'),
    ]);

    expect($survey->isClosed())->toBeFalse();
});

it('exposes all getters', function (): void {
    $survey = createSurvey([
        'cierraEl' => new \DateTimeImmutable('+3 days'),
    ]);

    expect($survey->id())->toBeInstanceOf(Uuid::class)
        ->and($survey->condominiumId())->toBeInstanceOf(Uuid::class)
        ->and($survey->pregunta())->toBeString()
        ->and($survey->tipo())->toBeInstanceOf(SurveyType::class)
        ->and($survey->cierraEl())->toBeInstanceOf(\DateTimeImmutable::class)
        ->and($survey->activa())->toBeBool()
        ->and($survey->createdAt())->toBeInstanceOf(\DateTimeImmutable::class)
        ->and($survey->updatedAt())->toBeInstanceOf(\DateTimeImmutable::class);
});
