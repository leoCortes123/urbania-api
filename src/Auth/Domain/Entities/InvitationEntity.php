<?php

declare(strict_types=1);

namespace Urbania\Auth\Domain\Entities;

use Urbania\Shared\Domain\ValueObjects\Uuid;

final class InvitationEntity
{
    private function __construct(
        private readonly Uuid $id,
        private readonly string $token,
        private readonly string $inviterUserId,
        private readonly string $propertyId,
        private readonly string $inviteeEmail,
        private readonly string $inviteeName,
        private readonly string $occupantTypeId,
        private string $status,
        private readonly \DateTimeImmutable $expiresAt,
        private readonly \DateTimeImmutable $createdAt,
        private \DateTimeImmutable $updatedAt,
        private ?\DateTimeImmutable $acceptedAt = null,
        private ?string $acceptedByUserId = null,
        private ?\DateTimeImmutable $revokedAt = null,
    ) {}

    public static function create(
        string $inviterUserId,
        string $propertyId,
        string $inviteeEmail,
        string $inviteeName,
        string $occupantTypeId,
    ): self {
        $now = new \DateTimeImmutable;

        return new self(
            id: Uuid::v7(),
            token: self::generateToken(),
            inviterUserId: $inviterUserId,
            propertyId: $propertyId,
            inviteeEmail: $inviteeEmail,
            inviteeName: $inviteeName,
            occupantTypeId: $occupantTypeId,
            status: 'pending',
            expiresAt: $now->modify('+72 hours'),
            createdAt: $now,
            updatedAt: $now,
        );
    }

    public static function reconstitute(
        string $id,
        string $token,
        string $inviterUserId,
        string $propertyId,
        string $inviteeEmail,
        string $inviteeName,
        string $occupantTypeId,
        string $status,
        \DateTimeImmutable $expiresAt,
        \DateTimeImmutable $createdAt,
        \DateTimeImmutable $updatedAt,
        ?\DateTimeImmutable $acceptedAt = null,
        ?string $acceptedByUserId = null,
        ?\DateTimeImmutable $revokedAt = null,
    ): self {
        return new self(
            id: Uuid::fromString($id),
            token: $token,
            inviterUserId: $inviterUserId,
            propertyId: $propertyId,
            inviteeEmail: $inviteeEmail,
            inviteeName: $inviteeName,
            occupantTypeId: $occupantTypeId,
            status: $status,
            expiresAt: $expiresAt,
            acceptedAt: $acceptedAt,
            acceptedByUserId: $acceptedByUserId,
            revokedAt: $revokedAt,
            createdAt: $createdAt,
            updatedAt: $updatedAt,
        );
    }

    public function markAsAccepted(string $acceptedByUserId): void
    {
        $this->status = 'accepted';
        $this->acceptedAt = new \DateTimeImmutable;
        $this->acceptedByUserId = $acceptedByUserId;
        $this->updatedAt = new \DateTimeImmutable;
    }

    public function revoke(): void
    {
        $this->status = 'revoked';
        $this->revokedAt = new \DateTimeImmutable;
        $this->updatedAt = new \DateTimeImmutable;
    }

    public function isExpired(): bool
    {
        return $this->expiresAt <= new \DateTimeImmutable;
    }

    public function isPending(): bool
    {
        return $this->status === 'pending';
    }

    public function isAccepted(): bool
    {
        return $this->status === 'accepted';
    }

    public function isRevoked(): bool
    {
        return $this->status === 'revoked';
    }

    /**
     * Genera un token aleatorio URL-safe de 128 caracteres.
     */
    private static function generateToken(): string
    {
        return bin2hex(random_bytes(64));
    }

    public function id(): Uuid
    {
        return $this->id;
    }

    public function token(): string
    {
        return $this->token;
    }

    public function inviterUserId(): string
    {
        return $this->inviterUserId;
    }

    public function propertyId(): string
    {
        return $this->propertyId;
    }

    public function inviteeEmail(): string
    {
        return $this->inviteeEmail;
    }

    public function inviteeName(): string
    {
        return $this->inviteeName;
    }

    public function occupantTypeId(): string
    {
        return $this->occupantTypeId;
    }

    public function status(): string
    {
        return $this->status;
    }

    public function expiresAt(): \DateTimeImmutable
    {
        return $this->expiresAt;
    }

    public function acceptedAt(): ?\DateTimeImmutable
    {
        return $this->acceptedAt;
    }

    public function acceptedByUserId(): ?string
    {
        return $this->acceptedByUserId;
    }

    public function revokedAt(): ?\DateTimeImmutable
    {
        return $this->revokedAt;
    }

    public function createdAt(): \DateTimeImmutable
    {
        return $this->createdAt;
    }

    public function updatedAt(): \DateTimeImmutable
    {
        return $this->updatedAt;
    }
}
