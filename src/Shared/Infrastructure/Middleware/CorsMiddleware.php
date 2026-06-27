<?php

declare(strict_types=1);

namespace Urbania\Shared\Infrastructure\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

final class CorsMiddleware
{
    /**
     * @var array<string, string>
     */
    private const array HEADERS = [
        'Access-Control-Allow-Methods' => 'GET, POST, PUT, PATCH, DELETE, OPTIONS',
        'Access-Control-Allow-Headers' => 'Content-Type, Authorization, X-Trace-Id, X-Requested-With',
        'Access-Control-Allow-Credentials' => 'true',
        'Access-Control-Max-Age' => '86400',
    ];

    private const string DEFAULT_ORIGIN = 'http://localhost:5173';

    private string $allowedOrigins;

    public function __construct(?string $allowedOrigins = null)
    {
        $this->allowedOrigins = $allowedOrigins ?? $this->resolveAllowedOrigins();
    }

    public function handle(Request $request, Closure $next): Response
    {
        if ($request->isMethod('OPTIONS')) {
            return new Response('', 204, $this->corsHeaders());
        }

        /** @var Response $response */
        $response = $next($request);

        foreach ($this->corsHeaders() as $key => $value) {
            $response->headers->set($key, $value);
        }

        return $response;
    }

    /**
     * @return array<string, string>
     */
    private function corsHeaders(): array
    {
        return array_merge(
            [
                'Access-Control-Allow-Origin' => $this->allowedOrigins,
            ],
            self::HEADERS,
        );
    }

    private function resolveAllowedOrigins(): string
    {
        $configuredOrigins = config('cors.allowed_origins', self::DEFAULT_ORIGIN);

        return is_string($configuredOrigins) && $configuredOrigins !== ''
            ? $configuredOrigins
            : self::DEFAULT_ORIGIN;
    }
}
