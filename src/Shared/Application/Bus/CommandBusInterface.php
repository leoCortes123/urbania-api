<?php

declare(strict_types=1);

namespace Urbania\Shared\Application\Bus;

interface CommandBusInterface
{
    public function dispatch(object $command): mixed;
}
