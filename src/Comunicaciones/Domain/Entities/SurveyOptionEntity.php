<?php

declare(strict_types=1);

namespace Urbania\Comunicaciones\Domain\Entities;

use Urbania\Shared\Domain\ValueObjects\Uuid;

final readonly class SurveyOptionEntity
{
    private function __construct(
        private Uuid $id,
        private Uuid $surveyId,
        private string $texto,
        private int $orden,
        private \DateTimeImmutable $createdAt,
        private \DateTimeImmutable $updatedAt,
    ) {}

    public static function create(
        Uuid $surveyId,
        string $texto,
        int $orden = 0,
    ): self {
        $now = new \DateTimeImmutable;

        return new self(
            Uuid::v7(),
            $surveyId,
            $texto,
            $orden,
            $now,
            $now,
        );
    }

    public static function reconstitute(
        Uuid $id,
        Uuid $surveyId,
        string $texto,
        int $orden,
        \DateTimeImmutable $createdAt,
        \DateTimeImmutable $updatedAt,
    ): self {
        return new self(
            $id,
            $surveyId,
            $texto,
            $orden,
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

    public function texto(): string
    {
        return $this->texto;
    }

    public function orden(): int
    {
        return $this->orden;
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
