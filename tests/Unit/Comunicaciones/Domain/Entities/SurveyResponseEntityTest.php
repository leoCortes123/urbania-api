<?php

declare(strict_types=1);

namespace Tests\Unit\Comunicaciones\Domain\Entities;

use Urbania\Comunicaciones\Domain\Entities\SurveyResponseEntity;
use Urbania\Shared\Domain\ValueObjects\Uuid;

function createSurveyResponse(array $overrides = []): SurveyResponseEntity
{
    return SurveyResponseEntity::create(
        $overrides['surveyId'] ?? Uuid::v7(),
        $overrides['contactId'] ?? Uuid::v7(),
        $overrides['optionId'] ?? Uuid::v7(),
    );
}

it('create() creates response with UUID generated and fechas now', function (): void {
    $surveyId = Uuid::v7();
    $contactId = Uuid::v7();
    $optionId = Uuid::v7();
    $response = createSurveyResponse([
        'surveyId' => $surveyId,
        'contactId' => $contactId,
        'optionId' => $optionId,
    ]);

    expect($response->id())->toBeInstanceOf(Uuid::class)
        ->and($response->surveyId()->toString())->toBe($surveyId->toString())
        ->and($response->contactId()->toString())->toBe($contactId->toString())
        ->and($response->optionId()->toString())->toBe($optionId->toString())
        ->and($response->createdAt())->toBeInstanceOf(\DateTimeImmutable::class)
        ->and($response->updatedAt())->toBeInstanceOf(\DateTimeImmutable::class);
});

it('reconstitute() restores entity with exact values', function (): void {
    $id = Uuid::v7();
    $surveyId = Uuid::v7();
    $contactId = Uuid::v7();
    $optionId = Uuid::v7();
    $createdAt = new \DateTimeImmutable('-1 day');
    $updatedAt = new \DateTimeImmutable('-1 hour');

    $response = SurveyResponseEntity::reconstitute(
        $id,
        $surveyId,
        $contactId,
        $optionId,
        $createdAt,
        $updatedAt,
    );

    expect($response->id()->toString())->toBe($id->toString())
        ->and($response->surveyId()->toString())->toBe($surveyId->toString())
        ->and($response->contactId()->toString())->toBe($contactId->toString())
        ->and($response->optionId()->toString())->toBe($optionId->toString())
        ->and($response->createdAt())->toBe($createdAt)
        ->and($response->updatedAt())->toBe($updatedAt);
});

it('exposes all getters', function (): void {
    $response = createSurveyResponse();

    expect($response->id())->toBeInstanceOf(Uuid::class)
        ->and($response->surveyId())->toBeInstanceOf(Uuid::class)
        ->and($response->contactId())->toBeInstanceOf(Uuid::class)
        ->and($response->optionId())->toBeInstanceOf(Uuid::class)
        ->and($response->createdAt())->toBeInstanceOf(\DateTimeImmutable::class)
        ->and($response->updatedAt())->toBeInstanceOf(\DateTimeImmutable::class);
});
