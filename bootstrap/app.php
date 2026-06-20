<?php

declare(strict_types=1);

use Illuminate\Auth\Access\AuthorizationException;
use Illuminate\Auth\AuthenticationException;
use Illuminate\Foundation\Application;
use Illuminate\Foundation\Configuration\Exceptions;
use Illuminate\Foundation\Configuration\Middleware;
use Illuminate\Http\Exceptions\ThrottleRequestsException;
use Illuminate\Http\Request;
use Illuminate\Validation\ValidationException;
use Ramsey\Uuid\Uuid;
use Symfony\Component\HttpKernel\Exception\AccessDeniedHttpException;
use Symfony\Component\HttpKernel\Exception\HttpExceptionInterface;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;
use Urbania\Auth\Infrastructure\Http\Middleware\JwtAuthenticate;
use Urbania\Shared\Domain\Exceptions\DomainException;
use Urbania\Shared\Infrastructure\Middleware\RequestLoggingMiddleware;
use Urbania\Shared\Infrastructure\Middleware\SecurityHeadersMiddleware;
use Urbania\Shared\Infrastructure\Middleware\TraceIdMiddleware;

return Application::configure(basePath: dirname(__DIR__))
    ->withRouting(
        web: __DIR__.'/../routes/web.php',
        api: __DIR__.'/../routes/api.php',
        commands: __DIR__.'/../routes/console.php',
        health: '/up',
    )
    ->withMiddleware(function (Middleware $middleware): void {
        $middleware->prependToGroup('api', [
            TraceIdMiddleware::class,
            RequestLoggingMiddleware::class,
        ]);

        $middleware->appendToGroup('api', [
            SecurityHeadersMiddleware::class,
        ]);

        $middleware->alias([
            'jwt.auth' => JwtAuthenticate::class,
        ]);
    })
    ->withExceptions(function (Exceptions $exceptions): void {
        $exceptions->render(function (Throwable $e, Request $request) {
            if ($request->is('api/*') || $request->wantsJson()) {
                $traceId = $request->attributes->get('trace_id', (string) Uuid::uuid7());

                $status = 500;
                $code = 'INTERNAL_ERROR';
                $message = $e->getMessage() ?: 'Error interno del servidor';

                if ($e instanceof DomainException) {
                    $code = $e->errorCode;
                    $status = $e->httpStatusCode;
                    $message = $e->getMessage();
                } elseif ($e instanceof ValidationException) {
                    $code = 'VALIDATION_ERROR';
                    $status = 422;
                    $message = $e->getMessage();
                } elseif ($e instanceof AuthenticationException) {
                    $code = 'UNAUTHORIZED';
                    $status = 401;
                    $message = 'No autenticado';
                } elseif ($e instanceof AuthorizationException || $e instanceof AccessDeniedHttpException) {
                    $code = 'FORBIDDEN';
                    $status = 403;
                    $message = $e->getMessage() ?: 'Acceso denegado';
                } elseif ($e instanceof ThrottleRequestsException) {
                    return response()->json([
                        'error' => [
                            'code' => 'RATE_LIMIT_EXCEEDED',
                            'message' => 'Demasiadas peticiones. Intenta de nuevo más tarde.',
                            'trace_id' => $traceId,
                        ],
                    ], 429)->withHeaders($e->getHeaders());
                } elseif ($e instanceof NotFoundHttpException) {
                    $code = 'USER_NOT_FOUND';
                    $status = 404;
                    $message = 'Recurso no encontrado';
                } elseif ($e instanceof HttpExceptionInterface) {
                    $status = $e->getStatusCode();
                    $code = 'HTTP_ERROR';
                    $message = $e->getMessage();
                }

                return response()->json([
                    'error' => [
                        'code' => $code,
                        'message' => $message,
                        'trace_id' => $traceId,
                    ],
                ], $status);
            }

            return null;
        });
    })->create();
