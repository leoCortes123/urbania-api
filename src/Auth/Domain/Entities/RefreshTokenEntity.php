<?php

declare(strict_types=1);

namespace Urbania\Auth\Domain\Entities;

use Urbania\Auth\Domain\ValueObjects\DeviceFingerprint;
use Urbania\Auth\Domain\ValueObjects\SessionId;
use Urbania\Shared\Domain\ValueObjects\Uuid;

final class RefreshTokenEntity
{
    private Uuid $id;

    private Uuid $userId;

    private SessionId $sessionId;

    private string $tokenHash;

    private Uuid $tokenFamily;

    private ?string $previousTokenHash;

    private ?DeviceFingerprint $deviceFingerprint;

    private ?string $deviceName;

    private ?string $ipAddress;

    private ?string $userAgent;

    private \DateTimeImmutable $expiresAt;

    private ?\DateTimeImmutable $revokedAt;

    private ?string $revocationReason;

    private ?\DateTimeImmutable $lastUsedAt;

    private \DateTimeImmutable $createdAt;

    private function __construct(
        Uuid $id,
        Uuid $userId,
        SessionId $sessionId,
        string $tokenHash,
        Uuid $tokenFamily,
        ?string $previousTokenHash,
        ?DeviceFingerprint $deviceFingerprint,
        ?string $deviceName,
        ?string $ipAddress,
        ?string $userAgent,
        \DateTimeImmutable $expiresAt,
        ?\DateTimeImmutable $revokedAt,
        ?string $revocationReason,
        ?\DateTimeImmutable $lastUsedAt,
        \DateTimeImmutable $createdAt,
    ) {
        $this->id = $id;
        $this->userId = $userId;
        $this->sessionId = $sessionId;
        $this->tokenHash = $tokenHash;
        $this->tokenFamily = $tokenFamily;
        $this->previousTokenHash = $previousTokenHash;
        $this->deviceFingerprint = $deviceFingerprint;
        $this->deviceName = $deviceName;
        $this->ipAddress = $ipAddress;
        $this->userAgent = $userAgent;
        $this->expiresAt = $expiresAt;
        $this->revokedAt = $revokedAt;
        $this->revocationReason = $revocationReason;
        $this->lastUsedAt = $lastUsedAt;
        $this->createdAt = $createdAt;
    }

    public static function create(
        Uuid $userId,
        SessionId $sessionId,
        string $tokenHash,
        Uuid $tokenFamily,
        \DateTimeImmutable $expiresAt,
        ?string $previousTokenHash = null,
        ?DeviceFingerprint $deviceFingerprint = null,
        ?string $deviceName = null,
        ?string $ipAddress = null,
        ?string $userAgent = null,
    ): self {
        return new self(
            Uuid::v7(),
            $userId,
            $sessionId,
            $tokenHash,
            $tokenFamily,
            $previousTokenHash,
            $deviceFingerprint,
            $deviceName,
            $ipAddress,
            $userAgent,
            $expiresAt,
            null,
            null,
            null,
            new \DateTimeImmutable,
        );
    }

    public function revoke(string $reason): void
    {
        $this->revokedAt = new \DateTimeImmutable;
        $this->revocationReason = $reason;
    }

    public function isExpired(): bool
    {
        return $this->expiresAt <= new \DateTimeImmutable;
    }

    public function isRevoked(): bool
    {
        return $this->revokedAt !== null;
    }

    public function markUsed(): void
    {
        $this->lastUsedAt = new \DateTimeImmutable;
    }

    public function id(): Uuid
    {
        return $this->id;
    }

    public function userId(): Uuid
    {
        return $this->userId;
    }

    public function sessionId(): SessionId
    {
        return $this->sessionId;
    }

    public function tokenHash(): string
    {
        return $this->tokenHash;
    }

    public function tokenFamily(): Uuid
    {
        return $this->tokenFamily;
    }

    public function previousTokenHash(): ?string
    {
        return $this->previousTokenHash;
    }

    public function deviceFingerprint(): ?DeviceFingerprint
    {
        return $this->deviceFingerprint;
    }

    public function deviceName(): ?string
    {
        return $this->deviceName;
    }

    public function ipAddress(): ?string
    {
        return $this->ipAddress;
    }

    public function userAgent(): ?string
    {
        return $this->userAgent;
    }

    public function expiresAt(): \DateTimeImmutable
    {
        return $this->expiresAt;
    }

    public function revokedAt(): ?\DateTimeImmutable
    {
        return $this->revokedAt;
    }

    public function revocationReason(): ?string
    {
        return $this->revocationReason;
    }

    public function lastUsedAt(): ?\DateTimeImmutable
    {
        return $this->lastUsedAt;
    }

    public function createdAt(): \DateTimeImmutable
    {
        return $this->createdAt;
    }
}
