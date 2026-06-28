<?php

declare(strict_types=1);

namespace Urbania\Propiedades\Domain\Entities;

use Urbania\Shared\Domain\ValueObjects\Uuid;

final class PropertyStatusEntity
{
    private function __construct(
        private Uuid $id,
        private string $code,
        private string $name,
        private ?string $description,
        private bool $allowsResidents,
        private bool $isActive,
        private int $sortOrder,
        private \DateTimeImmutable $createdAt,
        private \DateTimeImmutable $updatedAt,
    ) {}

    public static function create(
        string $code,
        string $name,
        bool $allowsResidents = true,
        ?string $description = null,
        int $sortOrder = 0,
    ): self {
        $now = new \DateTimeImmutable;

        return new self(
            Uuid::v7(),
            $code,
            $name,
            $description,
            $allowsResidents,
            true,
            $sortOrder,
            $now,
            $now,
        );
    }

    public static function reconstitute(
        Uuid $id,
        string $code,
        string $name,
        ?string $description,
        bool $allowsResidents,
        bool $isActive,
        int $sortOrder,
        \DateTimeImmutable $createdAt,
        \DateTimeImmutable $updatedAt,
    ): self {
        return new self(
            $id,
            $code,
            $name,
            $description,
            $allowsResidents,
            $isActive,
            $sortOrder,
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

    public function allowsResidents(): bool
    {
        return $this->allowsResidents;
    }

    public function isActive(): bool
    {
        return $this->isActive;
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

    public function update(
        string $name,
        ?string $description,
        bool $allowsResidents,
        int $sortOrder,
    ): void {
        $this->name = $name;
        $this->description = $description;
        $this->allowsResidents = $allowsResidents;
        $this->sortOrder = $sortOrder;
        $this->updatedAt = new \DateTimeImmutable;
    }

    public function updateCode(string $code): void
    {
        $this->code = $code;
        $this->updatedAt = new \DateTimeImmutable;
    }

    public function deactivate(): void
    {
        $this->isActive = false;
        $this->updatedAt = new \DateTimeImmutable;
    }
}
