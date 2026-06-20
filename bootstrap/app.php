<?php

declare(strict_types=1);

use Illuminate\Foundation\Application;
use Illuminate\Foundation\Configuration\Exceptions;
use Illuminate\Foundation\Configuration\Middleware;
use Illuminate\Http\Request;
use Illuminate\Support\Str;
use Illuminate\Validation\ValidationException;
use Symfony\Component\HttpKernel\Exception\HttpExceptionInterface;
use Urbania\Shared\Domain\Exceptions\DomainException;

return Application::configure(basePath: dirname(__DIR__))
    ->withRouting(
        web: __DIR__.'/../routes/web.php',
        api: __DIR__.'/../routes/api.php',
        commands: __DIR__.'/../routes/console.php',
        health: '/up',
    )
    ->withMiddleware(function (Middleware $middleware): void {
        //
    })
    ->withExceptions(function (Exceptions $exceptions): void {
        $exceptions->render(function (Throwable $e, Request $request) {
            if ($request->is('api/*') || $request->wantsJson()) {
                $traceId = $request->header('X-Trace-Id', (string) Str::uuid());

                $status = method_exists($e, 'getStatusCode')
                    ? $e->getStatusCode()
                    : 500;

                $code = 'INTERNAL_ERROR';
                if ($e instanceof DomainException) {
                    $code = $e->errorCode;
                    $status = $e->httpStatusCode;
                } elseif ($e instanceof ValidationException) {
                    $code = 'VALIDATION_ERROR';
                    $status = 422;
                } elseif ($e instanceof HttpExceptionInterface) {
                    $code = 'HTTP_'.$status;
                }

                return response()->json([
                    'error' => [
                        'code' => $code,
                        'message' => $e->getMessage(),
                        'trace_id' => $traceId,
                    ],
                ], $status);
            }

            return null;
        });
    })->create();
