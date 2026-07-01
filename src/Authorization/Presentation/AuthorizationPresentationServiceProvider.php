<?php

declare(strict_types=1);

namespace Urbania\Authorization\Presentation;

use Illuminate\Support\ServiceProvider;

final class AuthorizationPresentationServiceProvider extends ServiceProvider
{
    public function boot(): void
    {
        $this->loadRoutesFrom(__DIR__.'/routes.php');
    }
}
