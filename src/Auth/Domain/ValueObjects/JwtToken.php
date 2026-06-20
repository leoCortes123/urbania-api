<?php

declare(strict_types=1);

namespace Urbania\Auth\Domain\ValueObjects;

final readonly class JwtToken implements \Stringable
{
    private string $value;

    private function __construct(string $value)
    {
        $this->value = $value;
    }

    public static function fromString(string $token): self
    {
        $token = trim($token);
        if ($token === '') {
            throw new \InvalidArgumentException('JWT token cannot be empty');
        }

        if (str_starts_with(strtolower($token), 'bearer ')) {
            $token = substr($token, 7);
        }

        return new self($token);
    }

    public function toString(): string
    {
        return $this->value;
    }

    public function __toString(): string
    {
        return $this->toString();
    }
}
