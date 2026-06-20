<?php

declare(strict_types=1);

namespace Urbania\Auth\Domain\Events;

final readonly class SuspiciousActivityDetected
{
    /**
     * @param  array<string, mixed>  $details
     */
    public function __construct(
        public string $userId,
        public string $activityType,
        public string $ip,
        public array $details,
        public \DateTimeImmutable $timestamp,
    ) {}
}
