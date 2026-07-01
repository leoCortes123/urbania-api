<?php

declare(strict_types=1);

namespace Urbania\Comunicaciones\Application\DTOs;

use Urbania\Comunicaciones\Domain\ValueObjects\DeliveryChannel;
use Urbania\Comunicaciones\Domain\ValueObjects\Segment;
use Urbania\Shared\Domain\ValueObjects\Uuid;

final readonly class CreateAnnouncementDto
{
    /**
     * @param  array<DeliveryChannel>  $canales
     */
    public function __construct(
        public Uuid $condominiumId,
        public Uuid $autorUserId,
        public string $titulo,
        public string $cuerpo,
        public Segment $segmento,
        public ?Uuid $targetId,
        public array $canales,
        public ?\DateTimeImmutable $programadoPara,
        public bool $fijado,
    ) {}
}
