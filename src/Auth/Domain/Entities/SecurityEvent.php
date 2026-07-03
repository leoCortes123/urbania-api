<?php

declare(strict_types=1);

namespace Urbania\Auth\Domain\Entities;

use Urbania\Shared\Domain\ValueObjects\Uuid;

final class SecurityEvent
{
    private Uuid $id;

    private ?string $userId;

    private string $eventType;

    private string $severity;

    private string $ipAddress;

    private ?string $userAgent;

    /** @var array<string, mixed> */
    private array $details;

    private \DateTimeImmutable $createdAt;

    /**
     * @param array<string, mixed> $details
     */
    private function __construct(
        Uuid $id,
        ?string $userId,
        string $eventType,
        string $severity,
        string $ipAddress,
        ?string $userAgent,
        array $details,
        \DateTimeImmutable $createdAt,
    ) {
        $this->id = $id;
        $this->userId = $userId;
        $this->eventType = $eventType;
        $this->severity = $severity;
        $this->ipAddress = $ipAddress;
        $this->userAgent = $userAgent;
        $this->details = $details;
        $this->createdAt = $createdAt;
    }

    /**
     * @param array<string, mixed> $details
     */
    public static function create(
        ?string $userId,
        string $eventType,
        string $severity,
        string $ipAddress,
        ?string $userAgent,
        array $details = [],
    ): self {
        return new self(
            Uuid::v7(),
            $userId,
            $eventType,
            $severity,
            $ipAddress,
            $userAgent,
            $details,
            new \DateTimeImmutable,
        );
    }

    public function id(): Uuid
    {
        return $this->id;
    }

    public function userId(): ?string
    {
        return $this->userId;
    }

    public function eventType(): string
    {
        return $this->eventType;
    }

    public function severity(): string
    {
        return $this->severity;
    }

    public function ipAddress(): string
    {
        return $this->ipAddress;
    }

    public function userAgent(): ?string
    {
        return $this->userAgent;
    }

    /**
     * @return array<string, mixed>
     */
    public function details(): array
    {
        return $this->details;
    }

    public function createdAt(): \DateTimeImmutable
    {
        return $this->createdAt;
    }
}
