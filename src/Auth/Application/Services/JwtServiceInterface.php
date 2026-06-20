<?php

declare(strict_types=1);

namespace Urbania\Auth\Application\Services;

use Urbania\Auth\Domain\ValueObjects\JwtToken;
use Urbania\Auth\Domain\ValueObjects\SessionId;

interface JwtServiceInterface
{
    public function generateAccessToken(
        string $userId,
        string $role,
        bool $mfaVerified,
        SessionId $sessionId,
        string $deviceFingerprint,
        ?string $scope = null,
        ?int $ttl = null,
    ): JwtToken;

    public function generateRefreshToken(): string;

    /**
     * @return array<string, mixed>
     */
    public function decode(string $token): array;

    public function validate(string $token): bool;

    public function revoke(string $jti): void;

    public function isBlacklisted(string $jti): bool;
}
