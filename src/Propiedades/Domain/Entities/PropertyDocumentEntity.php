<?php

declare(strict_types=1);

namespace Urbania\Propiedades\Domain\Entities;

use Urbania\Shared\Domain\ValueObjects\Uuid;

final class PropertyDocumentEntity
{
    private function __construct(
        private Uuid $id,
        private Uuid $propertyId,
        private Uuid $propertyDocumentTypeId,
        private string $name,
        private string $fileUrl,
        private ?int $fileSizeBytes,
        private ?string $mimeType,
        private ?string $notes,
        private Uuid $uploadedByUserId,
        private \DateTimeImmutable $createdAt,
        private ?\DateTimeImmutable $deletedAt,
    ) {}

    public static function create(
        Uuid $propertyId,
        Uuid $propertyDocumentTypeId,
        string $name,
        string $fileUrl,
        Uuid $uploadedByUserId,
        ?int $fileSizeBytes = null,
        ?string $mimeType = null,
        ?string $notes = null,
    ): self {
        return new self(
            Uuid::v7(),
            $propertyId,
            $propertyDocumentTypeId,
            $name,
            $fileUrl,
            $fileSizeBytes,
            $mimeType,
            $notes,
            $uploadedByUserId,
            new \DateTimeImmutable,
            null,
        );
    }

    public static function reconstitute(
        Uuid $id,
        Uuid $propertyId,
        Uuid $propertyDocumentTypeId,
        string $name,
        string $fileUrl,
        ?int $fileSizeBytes,
        ?string $mimeType,
        ?string $notes,
        Uuid $uploadedByUserId,
        \DateTimeImmutable $createdAt,
        ?\DateTimeImmutable $deletedAt,
    ): self {
        return new self(
            $id,
            $propertyId,
            $propertyDocumentTypeId,
            $name,
            $fileUrl,
            $fileSizeBytes,
            $mimeType,
            $notes,
            $uploadedByUserId,
            $createdAt,
            $deletedAt,
        );
    }

    public function id(): Uuid
    {
        return $this->id;
    }

    public function propertyId(): Uuid
    {
        return $this->propertyId;
    }

    public function propertyDocumentTypeId(): Uuid
    {
        return $this->propertyDocumentTypeId;
    }

    public function name(): string
    {
        return $this->name;
    }

    public function fileUrl(): string
    {
        return $this->fileUrl;
    }

    public function fileSizeBytes(): ?int
    {
        return $this->fileSizeBytes;
    }

    public function mimeType(): ?string
    {
        return $this->mimeType;
    }

    public function notes(): ?string
    {
        return $this->notes;
    }

    public function uploadedByUserId(): Uuid
    {
        return $this->uploadedByUserId;
    }

    public function createdAt(): \DateTimeImmutable
    {
        return $this->createdAt;
    }

    public function deletedAt(): ?\DateTimeImmutable
    {
        return $this->deletedAt;
    }
}
