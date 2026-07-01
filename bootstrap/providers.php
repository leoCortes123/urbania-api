<?php

declare(strict_types=1);

use App\Providers\AppServiceProvider;
use Directorio\Presentation\DirectorioServiceProvider;
use Urbania\Auth\Presentation\UrbaniaAuthServiceProvider;
use Urbania\Authorization\Infrastructure\AuthorizationServiceProvider;
use Urbania\Authorization\Presentation\AuthorizationPresentationServiceProvider;
use Urbania\Comunicaciones\Presentation\ComunicacionesServiceProvider;
use Urbania\Propiedades\Presentation\UrbaniaPropiedadesServiceProvider;

return [
    AppServiceProvider::class,
    UrbaniaAuthServiceProvider::class,
    DirectorioServiceProvider::class,
    UrbaniaPropiedadesServiceProvider::class,
    AuthorizationServiceProvider::class,
    AuthorizationPresentationServiceProvider::class,
    ComunicacionesServiceProvider::class,
];
