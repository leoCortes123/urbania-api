<?php

declare(strict_types=1);

namespace Urbania\Auth\Domain\Exceptions;

use Urbania\Shared\Domain\Exceptions\DomainException;

final class InvitationAlreadyAcceptedException extends DomainException
{
    public function __construct(string $message = 'La invitación ya fue aceptada.')
    {
        parent::__construct('INVITATION_ALREADY_ACCEPTED', $message, 422);
    }
}
