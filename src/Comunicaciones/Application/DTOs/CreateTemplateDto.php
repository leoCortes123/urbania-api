<?php

declare(strict_types=1);

namespace Urbania\Comunicaciones\Application\DTOs;

use Urbania\Shared\Domain\ValueObjects\Uuid;

final readonly class CreateTemplateDto
{
    public function __construct(
        public Uuid $condominiumId,
        public string $nombre,
        public ?string $tipo,
        public string $cuerpo,
    ) {}
}
