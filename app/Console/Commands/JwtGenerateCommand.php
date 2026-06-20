<?php

declare(strict_types=1);

namespace App\Console\Commands;

use Illuminate\Console\Command;

final class JwtGenerateCommand extends Command
{
    protected $signature = 'jwt:generate';

    protected $description = 'Generate RSA key pair for JWT RS256 signing';

    public function handle(): int
    {
        $script = base_path('scripts/generate_jwt_keys.php');

        if (! is_file($script)) {
            $this->error("JWT key generation script not found: {$script}");

            return self::FAILURE;
        }

        require $script;

        return self::SUCCESS;
    }
}
