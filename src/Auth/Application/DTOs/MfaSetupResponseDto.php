<?php

declare(strict_types=1);

namespace Urbania\Auth\Application\DTOs;

final readonly class MfaSetupResponseDto
{
    /**
     * @param  list<string>  $backupCodes
     */
    public function __construct(
        public string $secret,
        public string $qrCodeUrl,
        public array $backupCodes,
    ) {}
}
