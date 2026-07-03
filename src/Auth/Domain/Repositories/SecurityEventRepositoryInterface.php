<?php

declare(strict_types=1);

namespace Urbania\Auth\Domain\Repositories;

use Urbania\Auth\Domain\Entities\SecurityEvent;

interface SecurityEventRepositoryInterface
{
    public function save(SecurityEvent $event): void;
}
