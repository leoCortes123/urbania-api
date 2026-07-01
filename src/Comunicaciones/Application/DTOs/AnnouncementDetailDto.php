<?php

declare(strict_types=1);

namespace Urbania\Comunicaciones\Application\DTOs;

final readonly class AnnouncementDetailDto
{
    /**
     * @param  array<string, mixed>  $breakdown
     */
    public function __construct(
        public AnnouncementDto $announcement,
        public array $breakdown,
    ) {}
}
