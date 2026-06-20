<?php

declare(strict_types=1);

namespace Urbania\Shared\Application\Bus;

interface EventBusInterface
{
    public function dispatch(object $event): void;
}
