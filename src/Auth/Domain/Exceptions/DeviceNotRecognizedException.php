<?php

declare(strict_types=1);

namespace Urbania\Auth\Domain\Exceptions;

use Urbania\Shared\Domain\Exceptions\DomainException;

final class DeviceNotRecognizedException extends DomainException
{
    public function __construct(string $message = 'Device not recognized')
    {
        parent::__construct('DEVICE_NOT_RECOGNIZED', $message, 403);
    }
}
