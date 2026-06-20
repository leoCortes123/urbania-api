<?php

declare(strict_types=1);

namespace Urbania\Shared\Domain\ValueObjects;

use Ramsey\Uuid\Uuid as RamseyUuid;
use Ramsey\Uuid\UuidInterface;

final readonly class Uuid implements \Stringable
{
    private UuidInterface $value;

    private function __construct(UuidInterface $value)
    {
        $this->value = $value;
    }

    public static function v7(): self
    {
        return new self(RamseyUuid::uuid7());
    }

    public static function fromString(string $uuid): self
    {
        if (! RamseyUuid::isValid($uuid)) {
            throw new \InvalidArgumentException("Invalid UUID: {$uuid}");
        }

        return new self(RamseyUuid::fromString($uuid));
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
