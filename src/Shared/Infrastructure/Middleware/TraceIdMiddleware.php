<?php

declare(strict_types=1);

namespace Urbania\Shared\Infrastructure\Middleware;

use Closure;
use Illuminate\Http\Request;
use Ramsey\Uuid\Uuid;
use Symfony\Component\HttpFoundation\Response;

final class TraceIdMiddleware
{
    public function handle(Request $request, Closure $next): Response
    {
        $traceId = $request->header('X-Trace-Id');

        if ($traceId === null || $traceId === '') {
            $traceId = (string) Uuid::uuid7();
        }

        $request->attributes->set('trace_id', $traceId);

        /** @var Response $response */
        $response = $next($request);

        $response->headers->set('X-Trace-Id', $traceId);

        return $response;
    }
}
