<?php

declare(strict_types=1);

namespace Urbania\Comunicaciones\Domain\Entities;

use Urbania\Comunicaciones\Domain\ValueObjects\DeliveryChannel;
use Urbania\Shared\Domain\ValueObjects\Uuid;

final readonly class CommunicationChannelEntity
{
    /**
     * @param  array<string, mixed>|null  $config
     */
    private function __construct(
        private Uuid $id,
        private Uuid $condominiumId,
        private DeliveryChannel $canal,
        private ?string $provider,
        private ?array $config,
        private bool $activo,
        private \DateTimeImmutable $createdAt,
        private \DateTimeImmutable $updatedAt,
    ) {}

    /**
     * @param  array<string, mixed>|null  $config
     */
    public static function create(
        Uuid $condominiumId,
        DeliveryChannel $canal,
        ?string $provider,
        ?array $config,
        bool $activo,
    ): self {
        $now = new \DateTimeImmutable;

        return new self(
            Uuid::v7(),
            $condominiumId,
            $canal,
            $provider,
            $config,
            $activo,
            $now,
            $now,
        );
    }

    /**
     * @param  array<string, mixed>|null  $config
     */
    public static function reconstitute(
        Uuid $id,
        Uuid $condominiumId,
        DeliveryChannel $canal,
        ?string $provider,
        ?array $config,
        bool $activo,
        \DateTimeImmutable $createdAt,
        \DateTimeImmutable $updatedAt,
    ): self {
        return new self(
            $id,
            $condominiumId,
            $canal,
            $provider,
            $config,
            $activo,
            $createdAt,
            $updatedAt,
        );
    }

    /**
     * @param  array<string, mixed>|null  $config
     */
    public function update(
        ?string $provider,
        ?array $config,
        bool $activo,
    ): self {
        return new self(
            $this->id,
            $this->condominiumId,
            $this->canal,
            $provider,
            $config,
            $activo,
            $this->createdAt,
            new \DateTimeImmutable,
        );
    }

    public function id(): Uuid
    {
        return $this->id;
    }

    public function condominiumId(): Uuid
    {
        return $this->condominiumId;
    }

    public function canal(): DeliveryChannel
    {
        return $this->canal;
    }

    public function provider(): ?string
    {
        return $this->provider;
    }

    /** @return array<string, mixed>|null */
    public function config(): ?array
    {
        return $this->config;
    }

    public function activo(): bool
    {
        return $this->activo;
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
