<?php

declare(strict_types=1);

use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;
use Urbania\Shared\Infrastructure\Middleware\CorsMiddleware;

it('returns 204 for options preflight requests with cors headers', function (): void {
    $middleware = new CorsMiddleware('http://localhost:5173');
    $request = Request::create('/api/v1/auth/login', 'OPTIONS');

    $response = $middleware->handle($request, fn (): Response => new Response('OK'));

    expect($response->getStatusCode())->toBe(204)
        ->and($response->headers->get('Access-Control-Allow-Origin'))->toBe('http://localhost:5173')
        ->and($response->headers->get('Access-Control-Allow-Methods'))->toBe('GET, POST, PUT, PATCH, DELETE, OPTIONS')
        ->and($response->headers->get('Access-Control-Allow-Headers'))->toBe('Content-Type, Authorization, X-Trace-Id, X-Requested-With')
        ->and($response->headers->get('Access-Control-Allow-Credentials'))->toBe('true')
        ->and($response->headers->get('Access-Control-Max-Age'))->toBe('86400')
        ->and($response->getContent())->toBe('');
});

it('attaches cors headers to every response', function (): void {
    $middleware = new CorsMiddleware('http://localhost:5173');
    $request = Request::create('/api/v1/auth/login', 'POST');

    $response = $middleware->handle($request, fn (): Response => new Response('OK', 200));

    expect($response->headers->get('Access-Control-Allow-Origin'))->toBe('http://localhost:5173')
        ->and($response->headers->get('Access-Control-Allow-Methods'))->toBe('GET, POST, PUT, PATCH, DELETE, OPTIONS')
        ->and($response->headers->get('Access-Control-Allow-Headers'))->toBe('Content-Type, Authorization, X-Trace-Id, X-Requested-With')
        ->and($response->headers->get('Access-Control-Allow-Credentials'))->toBe('true')
        ->and($response->headers->get('Access-Control-Max-Age'))->toBe('86400');
});

it('uses injected allowed origins', function (): void {
    $middleware = new CorsMiddleware('http://localhost:3000');
    $request = Request::create('/api/v1/auth/login', 'GET');

    $response = $middleware->handle($request, fn (): Response => new Response('OK'));

    expect($response->headers->get('Access-Control-Allow-Origin'))->toBe('http://localhost:3000');
});
