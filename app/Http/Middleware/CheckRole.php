<?php

declare(strict_types=1);

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class CheckRole
{
    /**
     * @param  Closure(Request): Response  $next
     * @param  array<int, string>  ...$roles
     */
    public function handle(Request $request, Closure $next, string ...$roles): Response
    {
        $user = $request->user();

        if ($user !== null) {
            /** @var mixed $roleValue */
            $roleValue = $user->role ?? null;
            $userRole = is_object($roleValue) && $roleValue instanceof \BackedEnum
                ? $roleValue->value
                : (is_string($roleValue) ? $roleValue : null);
        } else {
            $userRole = $request->attributes->get('auth_role');
        }

        if ($userRole === null || $userRole === '') {
            return new JsonResponse([
                'error' => [
                    'code' => 'UNAUTHENTICATED',
                    'message' => 'No autenticado',
                    'trace_id' => $request->header('X-Trace-Id', ''),
                ],
            ], 401);
        }

        foreach ($roles as $role) {
            if ($userRole === $role) {
                return $next($request);
            }
        }

        return new JsonResponse([
            'error' => [
                'code' => 'FORBIDDEN',
                'message' => 'No tienes permisos para acceder a este recurso',
                'trace_id' => $request->header('X-Trace-Id', ''),
            ],
        ], 403);
    }
}
