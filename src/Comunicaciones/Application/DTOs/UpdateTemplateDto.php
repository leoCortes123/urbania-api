<?php

declare(strict_types=1);

namespace Urbania\Comunicaciones\Application\DTOs;

final readonly class UpdateTemplateDto
{
    public function __construct(
        public ?string $nombre,
        public ?string $tipo,
        public ?string $cuerpo,
    ) {}
}
