<?php

declare(strict_types=1);

namespace Urbania\Propiedades\Domain\Entities;

use Urbania\Shared\Domain\ValueObjects\Uuid;

final class CondominiumEntity
{
    private function __construct(
        private Uuid $id,
        private string $name,
        private ?string $address,
        private ?string $city,
        private ?string $department,
        private string $country,
        private ?string $nit,
        private ?string $phone,
        private ?string $email,
        private ?string $legalRepresentative,
        private string $totalCoefficient,
        private ?string $logoUrl,
        private bool $isActive,
        private \DateTimeImmutable $createdAt,
        private \DateTimeImmutable $updatedAt,
        private ?\DateTimeImmutable $deletedAt,
    ) {}

    public static function create(
        string $name,
        ?string $address = null,
        ?string $city = null,
        ?string $department = null,
        ?string $country = null,
        ?string $nit = null,
        ?string $phone = null,
        ?string $email = null,
        ?string $legalRepresentative = null,
        ?string $totalCoefficient = null,
        ?string $logoUrl = null,
    ): self {
        $now = new \DateTimeImmutable;

        return new self(
            Uuid::v7(),
            $name,
            $address,
            $city,
            $department,
            $country ?? 'Colombia',
            $nit,
            $phone,
            $email,
            $legalRepresentative,
            $totalCoefficient ?? '1.000000',
            $logoUrl,
            true,
            $now,
            $now,
            null,
        );
    }

    public static function reconstitute(
        Uuid $id,
        string $name,
        ?string $address,
        ?string $city,
        ?string $department,
        string $country,
        ?string $nit,
        ?string $phone,
        ?string $email,
        ?string $legalRepresentative,
        string $totalCoefficient,
        ?string $logoUrl,
        bool $isActive,
        \DateTimeImmutable $createdAt,
        \DateTimeImmutable $updatedAt,
        ?\DateTimeImmutable $deletedAt,
    ): self {
        return new self(
            $id,
            $name,
            $address,
            $city,
            $department,
            $country,
            $nit,
            $phone,
            $email,
            $legalRepresentative,
            $totalCoefficient,
            $logoUrl,
            $isActive,
            $createdAt,
            $updatedAt,
            $deletedAt,
        );
    }

    public function id(): Uuid
    {
        return $this->id;
    }

    public function name(): string
    {
        return $this->name;
    }

    public function address(): ?string
    {
        return $this->address;
    }

    public function city(): ?string
    {
        return $this->city;
    }

    public function department(): ?string
    {
        return $this->department;
    }

    public function country(): string
    {
        return $this->country;
    }

    public function nit(): ?string
    {
        return $this->nit;
    }

    public function phone(): ?string
    {
        return $this->phone;
    }

    public function email(): ?string
    {
        return $this->email;
    }

    public function legalRepresentative(): ?string
    {
        return $this->legalRepresentative;
    }

    public function totalCoefficient(): string
    {
        return $this->totalCoefficient;
    }

    public function logoUrl(): ?string
    {
        return $this->logoUrl;
    }

    public function isActive(): bool
    {
        return $this->isActive;
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
