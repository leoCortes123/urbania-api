<?php

declare(strict_types=1);

namespace Urbania\Comunicaciones\Domain\Entities;

use Urbania\Shared\Domain\ValueObjects\Uuid;

final readonly class MessageTemplateEntity
{
    private function __construct(
        private Uuid $id,
        private Uuid $condominiumId,
        private string $nombre,
        private ?string $tipo,
        private string $cuerpo,
        private \DateTimeImmutable $createdAt,
        private \DateTimeImmutable $updatedAt,
        private ?\DateTimeImmutable $deletedAt,
    ) {}

    public static function create(
        Uuid $condominiumId,
        string $nombre,
        ?string $tipo,
        string $cuerpo,
    ): self {
        $now = new \DateTimeImmutable;

        return new self(
            Uuid::v7(),
            $condominiumId,
            $nombre,
            $tipo,
            $cuerpo,
            $now,
            $now,
            null,
        );
    }

    public static function reconstitute(
        Uuid $id,
        Uuid $condominiumId,
        string $nombre,
        ?string $tipo,
        string $cuerpo,
        \DateTimeImmutable $createdAt,
        \DateTimeImmutable $updatedAt,
        ?\DateTimeImmutable $deletedAt,
    ): self {
        return new self(
            $id,
            $condominiumId,
            $nombre,
            $tipo,
            $cuerpo,
            $createdAt,
            $updatedAt,
            $deletedAt,
        );
    }

    public function update(
        ?string $nombre = null,
        ?string $tipo = null,
        ?string $cuerpo = null,
    ): self {
        return new self(
            $this->id,
            $this->condominiumId,
            $nombre ?? $this->nombre,
            $tipo ?? $this->tipo,
            $cuerpo ?? $this->cuerpo,
            $this->createdAt,
            new \DateTimeImmutable,
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

    public function nombre(): string
    {
        return $this->nombre;
    }

    public function tipo(): ?string
    {
        return $this->tipo;
    }

    public function cuerpo(): string
    {
        return $this->cuerpo;
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
