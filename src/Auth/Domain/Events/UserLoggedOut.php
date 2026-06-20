<?php

declare(strict_types=1);

namespace Urbania\Auth\Domain\Events;

final readonly class UserLoggedOut
{
    public function __construct(
        public string $userId,
        public string $sessionId,
        public \DateTimeImmutable $timestamp,
    ) {}
}
