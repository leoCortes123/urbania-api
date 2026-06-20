<?php

declare(strict_types=1);

namespace Urbania\Auth\Presentation;

use Illuminate\Support\ServiceProvider;

final class UrbaniaAuthServiceProvider extends ServiceProvider
{
    public function register(): void
    {
        // Bindings se agregarán en sesiones futuras
    }

    public function boot(): void
    {
        $this->loadRoutesFrom(__DIR__.'/routes.php');
    }
}
