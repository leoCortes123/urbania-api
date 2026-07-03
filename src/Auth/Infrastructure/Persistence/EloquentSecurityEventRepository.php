<?php

declare(strict_types=1);

namespace Urbania\Auth\Infrastructure\Persistence;

use App\Models\SecurityEvent as SecurityEventModel;
use Urbania\Auth\Domain\Entities\SecurityEvent;
use Urbania\Auth\Domain\Repositories\SecurityEventRepositoryInterface;

final readonly class EloquentSecurityEventRepository implements SecurityEventRepositoryInterface
{
    public function save(SecurityEvent $event): void
    {
        SecurityEventModel::create([
            'id' => $event->id()->toString(),
            'user_id' => $event->userId(),
            'event_type' => $event->eventType(),
            'severity' => $event->severity(),
            'ip_address' => $event->ipAddress(),
            'user_agent' => $event->userAgent(),
            'details' => $event->details(),
            'created_at' => $event->createdAt(),
        ]);
    }
}
