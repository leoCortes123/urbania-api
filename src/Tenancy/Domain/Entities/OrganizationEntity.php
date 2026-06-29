<?php

declare(strict_types=1);

namespace Urbania\Tenancy\Domain\Entities;

use Urbania\Shared\Domain\ValueObjects\Uuid;

final readonly class OrganizationEntity
{
    public function __construct(
        private Uuid $id,
        private string $name,
        private string $type,
        private ?string $nit = null,
        private string $country = 'Colombia',
        private string $currency = 'COP',
        private string $status = 'trial',
    ) {}

    public function id(): Uuid
    {
        return $this->id;
    }

    public function name(): string
    {
        return $this->name;
    }

    public function type(): string
    {
        return $this->type;
    }

    public function nit(): ?string
    {
        return $this->nit;
    }

    public function country(): string
    {
        return $this->country;
    }

    public function currency(): string
    {
        return $this->currency;
    }

    public function status(): string
    {
        return $this->status;
    }

    public function isActive(): bool
    {
        return $this->status === 'activo';
    }

    public function isTrial(): bool
    {
        return $this->status === 'trial';
    }
}
