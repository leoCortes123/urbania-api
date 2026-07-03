<?php

declare(strict_types=1);

namespace Urbania\Auth\Application\DTOs;

final readonly class InvitationListItemDto
{
    public function __construct(
        public string $id,
        public string $inviteeEmail,
        public string $inviteeName,
        public string $occupantType,
        public string $status,
        public string $createdAt,
        public string $expiresAt,
        public ?string $acceptedAt,
    ) {}
}
