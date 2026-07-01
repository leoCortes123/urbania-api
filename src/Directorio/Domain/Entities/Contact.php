<?php

declare(strict_types=1);

namespace Directorio\Domain\Entities;

use Directorio\Domain\ValueObjects\DocumentNumber;
use Directorio\Domain\ValueObjects\DocumentType;

class Contact
{
    public function __construct(
        private readonly string $id,
        private readonly DocumentType $documentType,
        private readonly DocumentNumber $documentNumber,
        private readonly string $fullName,
        private readonly ?string $email = null,
        private readonly ?string $phone = null,
        private readonly ?string $emergencyContactName = null,
        private readonly ?string $emergencyContactPhone = null,
        private readonly ?string $notes = null,
        private readonly ?string $userId = null,
        private readonly ?string $organizationId = null,
        private readonly ?string $createdAt = null,
        private readonly ?string $updatedAt = null,
        private readonly ?string $deletedAt = null,
    ) {
        if (empty(trim($fullName))) {
            throw new \InvalidArgumentException('El nombre completo es requerido');
        }
    }

    public function id(): string
    {
        return $this->id;
    }

    public function documentType(): DocumentType
    {
        return $this->documentType;
    }

    public function documentNumber(): DocumentNumber
    {
        return $this->documentNumber;
    }

    public function fullName(): string
    {
        return $this->fullName;
    }

    public function email(): ?string
    {
        return $this->email;
    }

    public function phone(): ?string
    {
        return $this->phone;
    }

    public function emergencyContactName(): ?string
    {
        return $this->emergencyContactName;
    }

    public function emergencyContactPhone(): ?string
    {
        return $this->emergencyContactPhone;
    }

    public function notes(): ?string
    {
        return $this->notes;
    }

    public function userId(): ?string
    {
        return $this->userId;
    }

    public function organizationId(): ?string
    {
        return $this->organizationId;
    }

    public function createdAt(): ?string
    {
        return $this->createdAt;
    }

    public function updatedAt(): ?string
    {
        return $this->updatedAt;
    }

    public function deletedAt(): ?string
    {
        return $this->deletedAt;
    }

    public function isDeleted(): bool
    {
        return $this->deletedAt !== null;
    }
}
