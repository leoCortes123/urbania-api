<?php

declare(strict_types=1);

namespace Urbania\Comunicaciones\Domain\Entities;

use Urbania\Shared\Domain\ValueObjects\Uuid;

final readonly class SurveyResponseEntity
{
    private function __construct(
        private Uuid $id,
        private Uuid $surveyId,
        private Uuid $contactId,
        private Uuid $optionId,
        private \DateTimeImmutable $createdAt,
        private \DateTimeImmutable $updatedAt,
    ) {}

    public static function create(
        Uuid $surveyId,
        Uuid $contactId,
        Uuid $optionId,
    ): self {
        $now = new \DateTimeImmutable;

        return new self(
            Uuid::v7(),
            $surveyId,
            $contactId,
            $optionId,
            $now,
            $now,
        );
    }

    public static function reconstitute(
        Uuid $id,
        Uuid $surveyId,
        Uuid $contactId,
        Uuid $optionId,
        \DateTimeImmutable $createdAt,
        \DateTimeImmutable $updatedAt,
    ): self {
        return new self(
            $id,
            $surveyId,
            $contactId,
            $optionId,
            $createdAt,
            $updatedAt,
        );
    }

    public function id(): Uuid
    {
        return $this->id;
    }

    public function surveyId(): Uuid
    {
        return $this->surveyId;
    }

    public function contactId(): Uuid
    {
        return $this->contactId;
    }

    public function optionId(): Uuid
    {
        return $this->optionId;
    }

    public function createdAt(): \DateTimeImmutable
    {
        return $this->createdAt;
    }

    public function updatedAt(): \DateTimeImmutable
    {
        return $this->updatedAt;
    }
}
