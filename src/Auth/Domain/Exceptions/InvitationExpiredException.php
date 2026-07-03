<?php

declare(strict_types=1);

namespace Urbania\Auth\Domain\Exceptions;

use Urbania\Shared\Domain\Exceptions\DomainException;

final class InvitationExpiredException extends DomainException
{
    public function __construct(string $message = 'La invitación ha expirado (72h). Solicita una nueva invitación.')
    {
        parent::__construct('INVITATION_EXPIRED', $message, 422);
    }
}
