<?php

declare(strict_types=1);

namespace Urbania\Auth\Infrastructure\Services;

use Urbania\Auth\Application\Services\JwtServiceInterface;
use Urbania\Shared\Application\Services\TokenDecoderInterface;

final readonly class JwtTokenDecoder implements TokenDecoderInterface
{
    public function __construct(
        private JwtServiceInterface $jwtService,
    ) {}

    public function decode(string $token): array
    {
        return $this->jwtService->decode($token);
    }

    public function validate(string $token): bool
    {
        return $this->jwtService->validate($token);
    }
}
