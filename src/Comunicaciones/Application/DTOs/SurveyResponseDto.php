<?php

declare(strict_types=1);

namespace Urbania\Comunicaciones\Application\DTOs;

use Urbania\Comunicaciones\Domain\Entities\SurveyResponseEntity;

final readonly class SurveyResponseDto
{
    public function __construct(
        public string $id,
        public string $surveyId,
        public string $contactId,
        public string $optionId,
        public string $createdAt,
        public string $updatedAt,
    ) {}

    public static function fromEntity(SurveyResponseEntity $entity): self
    {
        return new self(
            id: $entity->id()->toString(),
            surveyId: $entity->surveyId()->toString(),
            contactId: $entity->contactId()->toString(),
            optionId: $entity->optionId()->toString(),
            createdAt: $entity->createdAt()->format('c'),
            updatedAt: $entity->updatedAt()->format('c'),
        );
    }
}
