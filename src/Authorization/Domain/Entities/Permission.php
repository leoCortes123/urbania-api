<?php

declare(strict_types=1);

namespace Urbania\Authorization\Domain\Entities;

use Urbania\Shared\Domain\ValueObjects\Uuid;

final readonly class Permission
{
    public function __construct(
        private Uuid $id,
        private string $resource,
        private string $action,
        private string $name,
    ) {}

    public function id(): Uuid
    {
        return $this->id;
    }

    public function resource(): string
    {
        return $this->resource;
    }

    public function action(): string
    {
        return $this->action;
    }

    public function name(): string
    {
        return $this->name;
    }

    public function matches(string $resource, string $action): bool
    {
        return $this->resource === $resource && $this->action === $action;
    }
}
