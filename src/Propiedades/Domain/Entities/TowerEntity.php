<?php

declare(strict_types=1);

namespace Urbania\Propiedades\Domain\Entities;

use Urbania\Shared\Domain\ValueObjects\Uuid;

final class TowerEntity
{
    private function __construct(
        private Uuid $id,
        private Uuid $condominiumId,
        private string $name,
        private ?string $code,
        private int $floorCount,
        private bool $hasElevator,
        private ?string $description,
        private int $sortOrder,
        private \DateTimeImmutable $createdAt,
        private \DateTimeImmutable $updatedAt,
        private ?\DateTimeImmutable $deletedAt,
    ) {}

    public static function create(
        Uuid $condominiumId,
        string $name,
        ?string $code = null,
        int $floorCount = 1,
        bool $hasElevator = false,
        ?string $description = null,
        int $sortOrder = 0,
    ): self {
        $now = new \DateTimeImmutable;

        return new self(
            Uuid::v7(),
            $condominiumId,
            $name,
            $code,
            $floorCount,
            $hasElevator,
            $description,
            $sortOrder,
            $now,
            $now,
            null,
        );
    }

    public static function reconstitute(
        Uuid $id,
        Uuid $condominiumId,
        string $name,
        ?string $code,
        int $floorCount,
        bool $hasElevator,
        ?string $description,
        int $sortOrder,
        \DateTimeImmutable $createdAt,
        \DateTimeImmutable $updatedAt,
        ?\DateTimeImmutable $deletedAt,
    ): self {
        return new self(
            $id,
            $condominiumId,
            $name,
            $code,
            $floorCount,
            $hasElevator,
            $description,
            $sortOrder,
            $createdAt,
            $updatedAt,
            $deletedAt,
        );
    }

    public function id(): Uuid
    {
        return $this->id;
    }

    public function condominiumId(): Uuid
    {
        return $this->condominiumId;
    }

    public function name(): string
    {
        return $this->name;
    }

    public function code(): ?string
    {
        return $this->code;
    }

    public function floorCount(): int
    {
        return $this->floorCount;
    }

    public function hasElevator(): bool
    {
        return $this->hasElevator;
    }

    public function description(): ?string
    {
        return $this->description;
    }

    public function sortOrder(): int
    {
        return $this->sortOrder;
    }

    public function createdAt(): \DateTimeImmutable
    {
        return $this->createdAt;
    }

    public function updatedAt(): \DateTimeImmutable
    {
        return $this->updatedAt;
    }

    public function deletedAt(): ?\DateTimeImmutable
    {
        return $this->deletedAt;
    }
}
