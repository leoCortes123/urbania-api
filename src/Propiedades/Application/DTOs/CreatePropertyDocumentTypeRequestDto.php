<?php

declare(strict_types=1);

namespace Urbania\Propiedades\Application\DTOs;

final readonly class CreatePropertyDocumentTypeRequestDto
{
    public function __construct(
        public string $code,
        public string $name,
        public ?string $description,
        public int $sortOrder,
    ) {}
}
