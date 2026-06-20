<?php

declare(strict_types=1);

namespace Urbania\Auth\Domain\ValueObjects;

enum UserStatus: string
{
    case ACTIVE = 'active';
    case SUSPENDED = 'suspended';
    case INACTIVE = 'inactive';
}
