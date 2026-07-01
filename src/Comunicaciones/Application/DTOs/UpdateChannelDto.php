<?php

declare(strict_types=1);

namespace Urbania\Comunicaciones\Application\DTOs;

use Urbania\Comunicaciones\Domain\ValueObjects\DeliveryChannel;
use Urbania\Shared\Domain\ValueObjects\Uuid;

final readonly class UpdateChannelDto
{
    /**
     * @param  array<string, mixed>|null  $config
     */
    public function __construct(
        public Uuid $condominiumId,
        public DeliveryChannel $canal,
        public ?string $provider,
        public ?array $config,
        public bool $activo,
    ) {}
}
