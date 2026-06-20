<?php

declare(strict_types=1);

namespace Urbania\Auth\Domain\ValueObjects;

enum UserRole: string
{
    case ADMIN = 'admin';
    case USER = 'user';
}
