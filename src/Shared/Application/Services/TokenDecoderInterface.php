<?php

declare(strict_types=1);

namespace Urbania\Shared\Application\Services;

interface TokenDecoderInterface
{
    /**
     * @return array<string, mixed>
     */
    public function decode(string $token): array;

    public function validate(string $token): bool;
}
