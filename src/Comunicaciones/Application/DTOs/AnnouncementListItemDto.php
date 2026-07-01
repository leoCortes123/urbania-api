<?php

declare(strict_types=1);

namespace Urbania\Comunicaciones\Application\DTOs;

final readonly class AnnouncementListItemDto
{
    /**
     * @param  array<string, mixed>  $metrics
     */
    public function __construct(
        public string $id,
        public string $titulo,
        public string $segmento,
        public string $estado,
        public ?string $programadoPara,
        public bool $fijado,
        public array $metrics,
    ) {}
}
