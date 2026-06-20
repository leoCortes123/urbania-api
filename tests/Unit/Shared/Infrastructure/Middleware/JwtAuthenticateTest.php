<?php

declare(strict_types=1);

use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;
use Urbania\Auth\Application\Services\JwtServiceInterface;
use Urbania\Auth\Domain\Exceptions\TokenExpiredException;
use Urbania\Auth\Domain\Exceptions\TokenInvalidException;
use Urbania\Auth\Infrastructure\Http\Middleware\JwtAuthenticate;

afterEach(function (): void {
    Mockery::close();
});

it('allows request with a valid token and extracts claims', function (): void {
    $jwtService = Mockery::mock(JwtServiceInterface::class);
    $jwtService->shouldReceive('validate')->once()->andReturn(true);
    $jwtService->shouldReceive('decode')->once()->andReturn([
        'sub' => '550e8400-e29b-41d4-a716-446655440000',
        'session_id' => '550e8400-e29b-41d4-a716-446655440001',
        'role' => 'user',
    ]);

    $middleware = new JwtAuthenticate($jwtService);
    $request = Request::create('/api/v1/auth/me', 'GET');
    $request->headers->set('Authorization', 'Bearer valid-token');

    $response = $middleware->handle($request, fn (): Response => new Response('OK'));

    expect($response->getStatusCode())->toBe(200);
    expect($request->attributes->get('auth_user_id'))->toBe('550e8400-e29b-41d4-a716-446655440000');
    expect($request->attributes->get('auth_session_id'))->toBe('550e8400-e29b-41d4-a716-446655440001');
    expect($request->attributes->get('auth_role'))->toBe('user');
});

it('throws token invalid exception when no token is provided', function (): void {
    $jwtService = Mockery::mock(JwtServiceInterface::class);
    $middleware = new JwtAuthenticate($jwtService);
    $request = Request::create('/api/v1/auth/me', 'GET');

    $middleware->handle($request, fn (): Response => new Response('OK'));
})->throws(TokenInvalidException::class);

it('throws token invalid exception for an invalid token', function (): void {
    $jwtService = Mockery::mock(JwtServiceInterface::class);
    $jwtService->shouldReceive('validate')->once()->andReturn(false);
    $jwtService->shouldReceive('decode')->once()->andThrow(new RuntimeException('Invalid token'));

    $middleware = new JwtAuthenticate($jwtService);
    $request = Request::create('/api/v1/auth/me', 'GET');
    $request->headers->set('Authorization', 'Bearer invalid-token');

    $middleware->handle($request, fn (): Response => new Response('OK'));
})->throws(TokenInvalidException::class);

it('throws token expired exception when token validates false but decodes', function (): void {
    $jwtService = Mockery::mock(JwtServiceInterface::class);
    $jwtService->shouldReceive('validate')->once()->andReturn(false);
    $jwtService->shouldReceive('decode')->once()->andReturn(['sub' => 'user-id']);

    $middleware = new JwtAuthenticate($jwtService);
    $request = Request::create('/api/v1/auth/me', 'GET');
    $request->headers->set('Authorization', 'Bearer expired-token');

    $middleware->handle($request, fn (): Response => new Response('OK'));
})->throws(TokenExpiredException::class);
