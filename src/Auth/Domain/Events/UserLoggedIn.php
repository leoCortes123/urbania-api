<?php

declare(strict_types=1);

namespace Urbania\Auth\Domain\Events;

final readonly class UserLoggedIn
{
    public function __construct(
        public string $userId,
        public string $ip,
        public ?string $deviceFp,
        public bool $mfaUsed,
        public \DateTimeImmutable $timestamp,
    ) {}
}
