<?php

declare(strict_types=1);

namespace Urbania\Auth\Domain\ValueObjects;

enum UserStatus: string
{
    case ACTIVE = 'active';
    case PENDING_ACTIVATION = 'pending_activation';
    case SUSPENDED = 'suspended';
    case INACTIVE = 'inactive';
}
