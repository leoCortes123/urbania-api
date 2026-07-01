<?php

declare(strict_types=1);

namespace Urbania\Comunicaciones\Domain\Entities;

use Urbania\Comunicaciones\Domain\ValueObjects\DeliveryChannel;
use Urbania\Comunicaciones\Domain\ValueObjects\DeliveryStatus;
use Urbania\Shared\Domain\ValueObjects\Uuid;

final readonly class AnnouncementDeliveryEntity
{
    /**
     * @param  array<string, mixed>|null  $metadata
     */
    private function __construct(
        private Uuid $id,
        private Uuid $announcementId,
        private Uuid $contactId,
        private DeliveryChannel $canal,
        private DeliveryStatus $estado,
        private ?string $externalId,
        private ?array $metadata,
        private \DateTimeImmutable $createdAt,
        private \DateTimeImmutable $updatedAt,
    ) {}

    /**
     * @param  array<string, mixed>|null  $metadata
     */
    public static function create(
        Uuid $announcementId,
        Uuid $contactId,
        DeliveryChannel $canal,
        DeliveryStatus $estado,
        ?string $externalId = null,
        ?array $metadata = null,
    ): self {
        $now = new \DateTimeImmutable;

        return new self(
            Uuid::v7(),
            $announcementId,
            $contactId,
            $canal,
            $estado,
            $externalId,
            $metadata,
            $now,
            $now,
        );
    }

    /**
     * @param  array<string, mixed>|null  $metadata
     */
    public static function reconstitute(
        Uuid $id,
        Uuid $announcementId,
        Uuid $contactId,
        DeliveryChannel $canal,
        DeliveryStatus $estado,
        ?string $externalId,
        ?array $metadata,
        \DateTimeImmutable $createdAt,
        \DateTimeImmutable $updatedAt,
    ): self {
        return new self(
            $id,
            $announcementId,
            $contactId,
            $canal,
            $estado,
            $externalId,
            $metadata,
            $createdAt,
            $updatedAt,
        );
    }

    /**
     * @param  array<string, mixed>|null  $metadata
     */
    public function withStatus(DeliveryStatus $estado, ?string $externalId = null, ?array $metadata = null): self
    {
        return new self(
            $this->id,
            $this->announcementId,
            $this->contactId,
            $this->canal,
            $estado,
            $externalId ?? $this->externalId,
            $metadata ?? $this->metadata,
            $this->createdAt,
            new \DateTimeImmutable,
        );
    }

    public function id(): Uuid
    {
        return $this->id;
    }

    public function announcementId(): Uuid
    {
        return $this->announcementId;
    }

    public function contactId(): Uuid
    {
        return $this->contactId;
    }

    public function canal(): DeliveryChannel
    {
        return $this->canal;
    }

    public function estado(): DeliveryStatus
    {
        return $this->estado;
    }

    public function externalId(): ?string
    {
        return $this->externalId;
    }

    /** @return array<string, mixed>|null */
    public function metadata(): ?array
    {
        return $this->metadata;
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
