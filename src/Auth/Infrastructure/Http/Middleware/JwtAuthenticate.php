<?php

declare(strict_types=1);

namespace Urbania\Auth\Infrastructure\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;
use Urbania\Auth\Application\Services\JwtServiceInterface;
use Urbania\Auth\Domain\Exceptions\TokenExpiredException;
use Urbania\Auth\Domain\Exceptions\TokenInvalidException;

final class JwtAuthenticate
{
    public function __construct(
        private readonly JwtServiceInterface $jwtService,
    ) {}

    public function handle(Request $request, Closure $next): Response
    {
        $token = $request->bearerToken();

        if ($token === null || $token === '') {
            throw new TokenInvalidException('Authorization token is required');
        }

        if (! $this->jwtService->validate($token)) {
            try {
                $this->jwtService->decode($token);
            } catch (\Throwable) {
                throw new TokenInvalidException;
            }

            throw new TokenExpiredException;
        }

        $payload = $this->jwtService->decode($token);

        $request->attributes->set('auth_user_id', $payload['sub'] ?? null);
        $request->attributes->set('auth_session_id', $payload['session_id'] ?? null);
        $request->attributes->set('auth_role', $payload['role'] ?? null);

        /** @var Response $response */
        $response = $next($request);

        return $response;
    }
}
