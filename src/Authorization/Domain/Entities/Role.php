<?php

declare(strict_types=1);

namespace Urbania\Authorization\Domain\Entities;

use Urbania\Shared\Domain\ValueObjects\Uuid;

final readonly class Role
{
    public function __construct(
        private Uuid $id,
        private string $name,
        private string $code,
        private string $level,
        private bool $isSystem,
        private ?Uuid $organizationId = null,
    ) {}

    public function id(): Uuid
    {
        return $this->id;
    }

    public function name(): string
    {
        return $this->name;
    }

    public function code(): string
    {
        return $this->code;
    }

    public function level(): string
    {
        return $this->level;
    }

    public function isSystem(): bool
    {
        return $this->isSystem;
    }

    public function organizationId(): ?Uuid
    {
        return $this->organizationId;
    }
}
