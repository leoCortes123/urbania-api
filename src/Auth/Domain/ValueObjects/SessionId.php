<?php

declare(strict_types=1);

namespace Urbania\Auth\Domain\ValueObjects;

use Urbania\Shared\Domain\ValueObjects\Uuid;

final readonly class SessionId implements \Stringable
{
    private Uuid $value;

    private function __construct(Uuid $value)
    {
        $this->value = $value;
    }

    public static function generate(): self
    {
        return new self(Uuid::v7());
    }

    public static function fromString(string $uuid): self
    {
        return new self(Uuid::fromString($uuid));
    }

    public static function fromUuid(Uuid $uuid): self
    {
        return new self($uuid);
    }

    public function equals(self $other): bool
    {
        return $this->value->equals($other->value);
    }

    public function toString(): string
    {
        return $this->value->toString();
    }

    public function __toString(): string
    {
        return $this->toString();
    }
}
