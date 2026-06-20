<?php

declare(strict_types=1);

use Urbania\Shared\Domain\Exceptions\DomainException;

arch()->preset()->php();

arch('Shared Domain does not depend on frameworks')
    ->expect('Urbania\Shared\Domain')
    ->not->toUse('Illuminate')
    ->not->toUse('Laravel');

arch('Auth Domain does not depend on Infrastructure')
    ->expect('Urbania\Auth\Domain')
    ->not->toUse('Urbania\Auth\Infrastructure');

arch('Auth Domain does not depend on frameworks')
    ->expect('Urbania\Auth\Domain')
    ->not->toUse('Illuminate')
    ->not->toUse('Laravel');

arch('Domain exceptions extend DomainException')
    ->expect('Urbania\Auth\Domain\Exceptions')
    ->toExtend(DomainException::class);

arch('Shared does not depend on Auth')
    ->expect('Urbania\Shared')
    ->not->toUse('Urbania\Auth');
