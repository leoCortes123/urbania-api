<?php

declare(strict_types=1);

namespace Urbania\Shared\Infrastructure\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Symfony\Component\HttpFoundation\Response;

final class RequestLoggingMiddleware
{
    public function handle(Request $request, Closure $next): Response
    {
        $start = microtime(true);

        /** @var Response $response */
        $response = $next($request);

        $duration = round((microtime(true) - $start) * 1000, 2);
        $traceId = $request->attributes->get('trace_id', 'unknown');
        $statusCode = $response->getStatusCode();

        $context = [
            'method' => $request->method(),
            'path' => $request->path(),
            'ip' => $request->ip(),
            'trace_id' => $traceId,
            'status' => $statusCode,
            'duration_ms' => $duration,
        ];

        if ($statusCode >= 500) {
            Log::error('Request completed with server error', $context);
        } elseif ($statusCode >= 400) {
            Log::warning('Request completed with client error', $context);
        } else {
            Log::info('Request completed', $context);
        }

        return $response;
    }
}
