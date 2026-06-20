<?php

declare(strict_types=1);

namespace Urbania\Auth\Application\DTOs;

final readonly class SessionResponseDto
{
    public function __construct(
        public string $sessionId,
        public ?string $deviceName,
        public string $deviceFingerprint,
        public string $ipAddress,
        public string $lastUsedAt,
        public string $createdAt,
        public bool $isCurrent,
    ) {}
}
