<?php

declare(strict_types=1);

namespace Urbania\Auth\Application\DTOs;

final readonly class CreateInvitationRequestDto
{
    public function __construct(
        public string $inviterUserId,
        public string $inviteeEmail,
        public string $inviteeName,
        public string $occupantTypeId,
    ) {}
}
