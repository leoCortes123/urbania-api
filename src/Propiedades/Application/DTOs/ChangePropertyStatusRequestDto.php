<?php

declare(strict_types=1);

namespace Urbania\Propiedades\Application\DTOs;

use Urbania\Shared\Domain\ValueObjects\Uuid;

final readonly class ChangePropertyStatusRequestDto
{
    public function __construct(
        public Uuid $propertyStatusId,
        public string $reason,
    ) {}
}
