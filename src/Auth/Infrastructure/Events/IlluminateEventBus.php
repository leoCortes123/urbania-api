<?php

declare(strict_types=1);

namespace Urbania\Auth\Infrastructure\Events;

use Illuminate\Contracts\Events\Dispatcher;
use Urbania\Shared\Application\Bus\EventBusInterface;

final readonly class IlluminateEventBus implements EventBusInterface
{
    public function __construct(
        private Dispatcher $dispatcher,
    ) {}

    public function dispatch(object $event): void
    {
        $this->dispatcher->dispatch($event);
    }
}
