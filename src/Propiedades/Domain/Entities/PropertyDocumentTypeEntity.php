<?php

declare(strict_types=1);

namespace Urbania\Propiedades\Domain\Entities;

use Urbania\Shared\Domain\ValueObjects\Uuid;

final class PropertyDocumentTypeEntity
{
    private function __construct(
        private Uuid $id,
        private string $code,
        private string $name,
        private ?string $description,
        private int $sortOrder,
        private bool $isActive,
        private \DateTimeImmutable $createdAt,
        private \DateTimeImmutable $updatedAt,
    ) {}

    public static function create(
        string $code,
        string $name,
        ?string $description = null,
        int $sortOrder = 0,
    ): self {
        $now = new \DateTimeImmutable;

        return new self(
            Uuid::v7(),
            $code,
            $name,
            $description,
            $sortOrder,
            true,
            $now,
            $now,
        );
    }

    public static function reconstitute(
        Uuid $id,
        string $code,
        string $name,
        ?string $description,
        int $sortOrder,
        bool $isActive,
        \DateTimeImmutable $createdAt,
        \DateTimeImmutable $updatedAt,
    ): self {
        return new self(
            $id,
            $code,
            $name,
            $description,
            $sortOrder,
            $isActive,
            $createdAt,
            $updatedAt,
        );
    }

    public function id(): Uuid
    {
        return $this->id;
    }

    public function code(): string
    {
        return $this->code;
    }

    public function name(): string
    {
        return $this->name;
    }

    public function description(): ?string
    {
        return $this->description;
    }

    public function sortOrder(): int
    {
        return $this->sortOrder;
    }

    public function isActive(): bool
    {
        return $this->isActive;
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
