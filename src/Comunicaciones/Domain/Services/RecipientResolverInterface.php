<?php

declare(strict_types=1);

namespace Urbania\Comunicaciones\Domain\Services;

use Urbania\Comunicaciones\Domain\ValueObjects\Segment;
use Urbania\Shared\Domain\ValueObjects\Uuid;

interface RecipientResolverInterface
{
    /**
     * Resuelve los IDs de contactos destinatarios para un segmento.
     *
     * @return array<Uuid>
     */
    public function resolve(Uuid $condominiumId, Segment $segmento, ?Uuid $targetId): array;
}
