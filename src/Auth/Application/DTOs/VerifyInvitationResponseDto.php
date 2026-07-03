<?php

declare(strict_types=1);

namespace Urbania\Auth\Application\DTOs;

final readonly class VerifyInvitationResponseDto
{
    public function __construct(
        public string $id,
        public string $inviterName,
        public string $propertyName,
        public string $inviteeEmail,
        public string $inviteeName,
        public string $occupantType,
        public string $status,
        public string $expiresAt,
    ) {}
}
