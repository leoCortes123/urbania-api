<?php

declare(strict_types=1);

namespace Urbania\Auth\Domain\Exceptions;

use Urbania\Shared\Domain\Exceptions\DomainException;

final class InvitationAlreadyPendingException extends DomainException
{
    public function __construct(string $message = 'Ya existe una invitación pendiente para este email en esta unidad.')
    {
        parent::__construct('INVITATION_ALREADY_PENDING', $message, 409);
    }
}
