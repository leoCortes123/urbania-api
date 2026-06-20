<?php

declare(strict_types=1);

$dirs = [
    'src/Shared/Domain/ValueObjects',
    'src/Shared/Domain/Exceptions',
    'src/Shared/Domain/Events',
    'src/Shared/Domain/Contracts',
    'src/Shared/Application/DTOs',
    'src/Shared/Application/Bus',
    'src/Shared/Infrastructure/Exceptions',
    'src/Shared/Infrastructure/Persistence',
    'src/Shared/Infrastructure/Bus',
    'src/Shared/Infrastructure/Logging',
    'src/Shared/Infrastructure/Middleware',
    'src/Auth/Domain/Entities',
    'src/Auth/Domain/ValueObjects',
    'src/Auth/Domain/Repositories',
    'src/Auth/Domain/Exceptions',
    'src/Auth/Domain/Events',
    'src/Auth/Application/DTOs',
    'src/Auth/Application/UseCases',
    'src/Auth/Application/Services',
    'src/Auth/Infrastructure/Persistence',
    'src/Auth/Infrastructure/Services',
    'src/Auth/Infrastructure/Mappers',
    'src/Auth/Infrastructure/Http/Controllers',
    'src/Auth/Infrastructure/Http/Requests',
    'src/Auth/Infrastructure/Http/Resources',
    'src/Auth/Presentation',
    'tests/Unit',
    'tests/Unit/Shared',
    'tests/Unit/Auth',
    'tests/Integration',
    'tests/Integration/Auth',
    'tests/Integration/Shared',
    'tests/Feature',
    'tests/Security',
    'tests/Architecture',
];

$base = dirname(__DIR__);

foreach ($dirs as $dir) {
    $path = $base.DIRECTORY_SEPARATOR.$dir;
    if (! is_dir($path) && ! mkdir($path, 0755, true) && ! is_dir($path)) {
        throw new RuntimeException("Failed to create directory: {$path}");
    }
}

echo "DDD directories created successfully.\n";
