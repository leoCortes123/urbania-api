<?php

declare(strict_types=1);

namespace Urbania\Comunicaciones\Domain\Entities;

use Urbania\Comunicaciones\Domain\ValueObjects\AnnouncementStatus;
use Urbania\Comunicaciones\Domain\ValueObjects\DeliveryChannel;
use Urbania\Comunicaciones\Domain\ValueObjects\Segment;
use Urbania\Shared\Domain\ValueObjects\Uuid;

final readonly class AnnouncementEntity
{
    /**
     * @param  array<DeliveryChannel>  $canales
     */
    private function __construct(
        private Uuid $id,
        private Uuid $condominiumId,
        private Uuid $autorUserId,
        private string $titulo,
        private string $cuerpo,
        private Segment $segmento,
        private ?Uuid $targetId,
        private AnnouncementStatus $estado,
        private ?\DateTimeImmutable $programadoPara,
        private bool $fijado,
        private array $canales,
        private \DateTimeImmutable $createdAt,
        private \DateTimeImmutable $updatedAt,
        private ?\DateTimeImmutable $deletedAt,
    ) {}

    /**
     * @param  array<DeliveryChannel>  $canales
     */
    public static function create(
        Uuid $condominiumId,
        Uuid $autorUserId,
        string $titulo,
        string $cuerpo,
        Segment $segmento,
        ?Uuid $targetId,
        AnnouncementStatus $estado,
        ?\DateTimeImmutable $programadoPara,
        bool $fijado,
        array $canales,
    ): self {
        $now = new \DateTimeImmutable;

        return new self(
            Uuid::v7(),
            $condominiumId,
            $autorUserId,
            $titulo,
            $cuerpo,
            $segmento,
            $targetId,
            $estado,
            $programadoPara,
            $fijado,
            $canales,
            $now,
            $now,
            null,
        );
    }

    /**
     * @param  array<DeliveryChannel>  $canales
     */
    public static function reconstitute(
        Uuid $id,
        Uuid $condominiumId,
        Uuid $autorUserId,
        string $titulo,
        string $cuerpo,
        Segment $segmento,
        ?Uuid $targetId,
        AnnouncementStatus $estado,
        ?\DateTimeImmutable $programadoPara,
        bool $fijado,
        array $canales,
        \DateTimeImmutable $createdAt,
        \DateTimeImmutable $updatedAt,
        ?\DateTimeImmutable $deletedAt,
    ): self {
        return new self(
            $id,
            $condominiumId,
            $autorUserId,
            $titulo,
            $cuerpo,
            $segmento,
            $targetId,
            $estado,
            $programadoPara,
            $fijado,
            $canales,
            $createdAt,
            $updatedAt,
            $deletedAt,
        );
    }

    public function markAsSent(): self
    {
        $now = new \DateTimeImmutable;

        return new self(
            $this->id,
            $this->condominiumId,
            $this->autorUserId,
            $this->titulo,
            $this->cuerpo,
            $this->segmento,
            $this->targetId,
            AnnouncementStatus::ENVIADO,
            $this->programadoPara,
            $this->fijado,
            $this->canales,
            $this->createdAt,
            $now,
            $this->deletedAt,
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

    public function autorUserId(): Uuid
    {
        return $this->autorUserId;
    }

    public function titulo(): string
    {
        return $this->titulo;
    }

    public function cuerpo(): string
    {
        return $this->cuerpo;
    }

    public function segmento(): Segment
    {
        return $this->segmento;
    }

    public function targetId(): ?Uuid
    {
        return $this->targetId;
    }

    public function estado(): AnnouncementStatus
    {
        return $this->estado;
    }

    public function programadoPara(): ?\DateTimeImmutable
    {
        return $this->programadoPara;
    }

    public function fijado(): bool
    {
        return $this->fijado;
    }

    /** @return array<DeliveryChannel> */
    public function canales(): array
    {
        return $this->canales;
    }

    public function createdAt(): \DateTimeImmutable
    {
        return $this->createdAt;
    }

    public function updatedAt(): \DateTimeImmutable
    {
        return $this->updatedAt;
    }

    public function deletedAt(): ?\DateTimeImmutable
    {
        return $this->deletedAt;
    }
}
