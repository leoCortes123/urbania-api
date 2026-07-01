<?php

declare(strict_types=1);

namespace Urbania\Comunicaciones\Domain\Entities;

use Urbania\Comunicaciones\Domain\ValueObjects\SurveyType;
use Urbania\Shared\Domain\ValueObjects\Uuid;

final readonly class SurveyEntity
{
    private function __construct(
        private Uuid $id,
        private Uuid $condominiumId,
        private string $pregunta,
        private SurveyType $tipo,
        private ?\DateTimeImmutable $cierraEl,
        private bool $activa,
        private \DateTimeImmutable $createdAt,
        private \DateTimeImmutable $updatedAt,
    ) {}

    public static function create(
        Uuid $condominiumId,
        string $pregunta,
        SurveyType $tipo,
        ?\DateTimeImmutable $cierraEl,
        bool $activa = true,
    ): self {
        $now = new \DateTimeImmutable;

        return new self(
            Uuid::v7(),
            $condominiumId,
            $pregunta,
            $tipo,
            $cierraEl,
            $activa,
            $now,
            $now,
        );
    }

    public static function reconstitute(
        Uuid $id,
        Uuid $condominiumId,
        string $pregunta,
        SurveyType $tipo,
        ?\DateTimeImmutable $cierraEl,
        bool $activa,
        \DateTimeImmutable $createdAt,
        \DateTimeImmutable $updatedAt,
    ): self {
        return new self(
            $id,
            $condominiumId,
            $pregunta,
            $tipo,
            $cierraEl,
            $activa,
            $createdAt,
            $updatedAt,
        );
    }

    public function isClosed(): bool
    {
        if ($this->cierraEl === null) {
            return ! $this->activa;
        }

        return $this->cierraEl < new \DateTimeImmutable || ! $this->activa;
    }

    public function id(): Uuid
    {
        return $this->id;
    }

    public function condominiumId(): Uuid
    {
        return $this->condominiumId;
    }

    public function pregunta(): string
    {
        return $this->pregunta;
    }

    public function tipo(): SurveyType
    {
        return $this->tipo;
    }

    public function cierraEl(): ?\DateTimeImmutable
    {
        return $this->cierraEl;
    }

    public function activa(): bool
    {
        return $this->activa;
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
