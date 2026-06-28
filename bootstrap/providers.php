<?php

declare(strict_types=1);

use App\Providers\AppServiceProvider;
use Directorio\Presentation\DirectorioServiceProvider;
use Urbania\Auth\Presentation\UrbaniaAuthServiceProvider;
use Urbania\Propiedades\Presentation\UrbaniaPropiedadesServiceProvider;

return [
    AppServiceProvider::class,
    UrbaniaAuthServiceProvider::class,
    DirectorioServiceProvider::class,
    UrbaniaPropiedadesServiceProvider::class,
];
