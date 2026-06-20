<?php

declare(strict_types=1);

namespace Urbania\Shared\Application\Bus;

interface QueryBusInterface
{
    public function ask(object $query): mixed;
}
