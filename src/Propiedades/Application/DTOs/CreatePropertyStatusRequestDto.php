<?php

declare(strict_types=1);

namespace Urbania\Propiedades\Application\DTOs;

final readonly class CreatePropertyStatusRequestDto
{
    public function __construct(
        public string $code,
        public string $name,
        public ?string $description,
        public bool $allowsResidents,
        public int $sortOrder,
    ) {}
}
