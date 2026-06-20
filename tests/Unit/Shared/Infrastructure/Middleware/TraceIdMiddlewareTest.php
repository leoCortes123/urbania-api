<?php

declare(strict_types=1);

use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;
use Urbania\Shared\Infrastructure\Middleware\TraceIdMiddleware;

it('generates a uuid v7 trace id when no header is provided', function (): void {
    $middleware = new TraceIdMiddleware;
    $request = Request::create('/api/v1/health', 'GET');

    $response = $middleware->handle($request, fn (): Response => new Response('OK'));

    $traceId = $request->attributes->get('trace_id');

    expect($traceId)->toBeString()->not->toBeEmpty();
    expect($response->headers->get('X-Trace-Id'))->toBe($traceId);
    expect($traceId)->toMatch('/^[0-9a-f]{8}-[0-9a-f]{4}-7[0-9a-f]{3}-[89ab][0-9a-f]{3}-[0-9a-f]{12}$/');
});

it('reuses the provided x-trace-id header', function (): void {
    $middleware = new TraceIdMiddleware;
    $request = Request::create('/api/v1/health', 'GET', [], [], [], ['HTTP_X_TRACE_ID' => '550e8400-e29b-41d4-a716-446655440000']);

    $response = $middleware->handle($request, fn (): Response => new Response('OK'));

    expect($request->attributes->get('trace_id'))->toBe('550e8400-e29b-41d4-a716-446655440000');
    expect($response->headers->get('X-Trace-Id'))->toBe('550e8400-e29b-41d4-a716-446655440000');
});
