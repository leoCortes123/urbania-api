<?php

declare(strict_types=1);

use App\Http\Middleware\CheckRole;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;
use Urbania\Auth\Domain\ValueObjects\UserRole;

it('allows request when jwt role matches one of the required roles', function (): void {
    $middleware = new CheckRole;
    $request = Request::create('/api/v1/admin/contacts', 'GET');
    $request->attributes->set('auth_role', 'admin');

    $response = $middleware->handle($request, fn (): Response => new Response('OK'), 'admin', 'superadmin');

    expect($response->getStatusCode())->toBe(200);
});

it('denies request with 403 when jwt role does not match any required role', function (): void {
    $middleware = new CheckRole;
    $request = Request::create('/api/v1/admin/contacts', 'GET');
    $request->attributes->set('auth_role', 'user');

    $response = $middleware->handle($request, fn (): Response => new Response('OK'), 'admin');

    expect($response->getStatusCode())->toBe(403);
    expect($response->getContent())->toContain('FORBIDDEN');
});

it('denies request with 401 when no authenticated user or jwt role is present', function (): void {
    $middleware = new CheckRole;
    $request = Request::create('/api/v1/admin/contacts', 'GET');

    $response = $middleware->handle($request, fn (): Response => new Response('OK'), 'admin');

    expect($response->getStatusCode())->toBe(401);
    expect($response->getContent())->toContain('UNAUTHENTICATED');
});

it('allows request when authenticated eloquent user has a matching role', function (): void {
    $middleware = new CheckRole;
    $request = Request::create('/api/v1/admin/contacts', 'GET');
    $user = new stdClass;
    $user->role = 'admin';
    $request->setUserResolver(fn (): stdClass => $user);

    $response = $middleware->handle($request, fn (): Response => new Response('OK'), 'admin');

    expect($response->getStatusCode())->toBe(200);
});

it('normalizes backed enum roles to their string value', function (): void {
    $middleware = new CheckRole;
    $request = Request::create('/api/v1/admin/contacts', 'GET');
    $user = new stdClass;
    $user->role = UserRole::ADMIN;
    $request->setUserResolver(fn (): stdClass => $user);

    $response = $middleware->handle($request, fn (): Response => new Response('OK'), 'admin');

    expect($response->getStatusCode())->toBe(200);
});
