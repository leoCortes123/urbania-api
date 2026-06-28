<?php

declare(strict_types=1);

namespace Urbania\Propiedades\Application\DTOs;

final readonly class UpdateCondominiumRequestDto
{
    public function __construct(
        public ?string $name,
        public ?string $address,
        public ?string $city,
        public ?string $department,
        public ?string $country,
        public ?string $nit,
        public ?string $phone,
        public ?string $email,
        public ?string $legalRepresentative,
        public ?string $logoUrl,
    ) {}
}
