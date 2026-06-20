<?php

declare(strict_types=1);

namespace Urbania\Auth\Domain\ValueObjects;

use Urbania\Auth\Domain\Exceptions\InvalidCredentialsException;

final readonly class Password
{
    private string $hash;

    private function __construct(string $hash)
    {
        $this->hash = $hash;
    }

    public static function fromPlainText(string $plainPassword): self
    {
        if (strlen($plainPassword) < 8) {
            throw InvalidCredentialsException::weakPassword();
        }

        $hash = password_hash($plainPassword, PASSWORD_ARGON2ID, [
            'memory_cost' => 65536,
            'time_cost' => 4,
            'threads' => 1,
        ]);

        return new self($hash);
    }

    public static function fromHash(string $hash): self
    {
        return new self($hash);
    }

    public function verify(string $plainPassword): bool
    {
        return password_verify($plainPassword, $this->hash);
    }

    public function needsRehash(): bool
    {
        return password_needs_rehash($this->hash, PASSWORD_ARGON2ID, [
            'memory_cost' => 65536,
            'time_cost' => 4,
            'threads' => 1,
        ]);
    }

    public function toString(): string
    {
        return $this->hash;
    }
}
