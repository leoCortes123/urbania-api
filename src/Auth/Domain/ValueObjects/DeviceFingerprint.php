<?php

declare(strict_types=1);

namespace Urbania\Auth\Domain\ValueObjects;

final readonly class DeviceFingerprint implements \Stringable
{
    private string $hash;

    private function __construct(string $hash)
    {
        $this->hash = $hash;
    }

    public static function calculate(string $userAgent, string $ip, string $acceptLanguage, string $deviceName): self
    {
        $raw = implode('|', [$userAgent, $ip, $acceptLanguage, $deviceName]);

        return new self(hash('sha256', $raw));
    }

    public static function fromHash(string $hash): self
    {
        if (! preg_match('/^[a-f0-9]{64}$/i', $hash)) {
            throw new \InvalidArgumentException('Invalid device fingerprint hash');
        }

        return new self($hash);
    }

    public function equals(self $other): bool
    {
        return hash_equals($this->hash, $other->hash);
    }

    public function toString(): string
    {
        return $this->hash;
    }

    public function __toString(): string
    {
        return $this->toString();
    }
}
