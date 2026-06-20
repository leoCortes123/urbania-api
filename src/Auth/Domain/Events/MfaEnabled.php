<?php

declare(strict_types=1);

namespace Urbania\Auth\Domain\Events;

final readonly class MfaEnabled
{
    public function __construct(
        public string $userId,
        public string $ip,
        public \DateTimeImmutable $timestamp,
    ) {}
}
