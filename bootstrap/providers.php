<?php

declare(strict_types=1);

use App\Providers\AppServiceProvider;
use Urbania\Auth\Presentation\UrbaniaAuthServiceProvider;

return [
    AppServiceProvider::class,
    UrbaniaAuthServiceProvider::class,
];
