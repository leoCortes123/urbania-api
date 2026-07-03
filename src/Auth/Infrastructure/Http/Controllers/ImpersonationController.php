<?php

declare(strict_types=1);

namespace Urbania\Auth\Infrastructure\Http\Controllers;

use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Routing\Controller;
use Urbania\Auth\Application\DTOs\StartImpersonationRequestDto;
use Urbania\Auth\Application\Services\JwtServiceInterface;
use Urbania\Auth\Application\UseCases\Impersonation\StartImpersonationUseCase;
use Urbania\Auth\Application\UseCases\Impersonation\StopImpersonationUseCase;
use Urbania\Auth\Domain\Exceptions\TokenInvalidException;
use Urbania\Auth\Infrastructure\Http\Requests\StartImpersonationRequest;

final class ImpersonationController extends Controller
{
    public function __construct(
        private readonly JwtServiceInterface $jwtService,
    ) {}

    public function impersonate(StartImpersonationRequest $request, StartImpersonationUseCase $useCase): JsonResponse
    {
        /** @var string $targetUserId */
        $targetUserId = $request->validated('user_id');
        /** @var string $reason */
        $reason = $request->validated('reason');

        // Get admin user ID from the JWT token attributes set by JwtAuthenticate
        /** @var string|null $adminUserId */
        $adminUserId = $request->attributes->get('auth_user_id');

        if ($adminUserId === null || $adminUserId === '') {
            return response()->json([
                'error' => [
                    'code' => 'UNAUTHORIZED',
                    'message' => 'No autenticado',
                    'trace_id' => $request->attributes->get('trace_id'),
                ],
            ], 401);
        }

        $dto = new StartImpersonationRequestDto(
            userId: $targetUserId,
            reason: $reason,
            adminUserId: $adminUserId,
            adminIp: $request->ip() ?? 'unknown',
        );

        $result = $useCase->execute($dto);

        return response()->json([
            'data' => [
                'token' => $result->token,
                'expires_in' => $result->expiresIn,
                'session_id' => $result->sessionId,
            ],
            'meta' => [
                'trace_id' => $request->attributes->get('trace_id'),
            ],
        ], 201);
    }

    public function stop(Request $request, StopImpersonationUseCase $useCase): JsonResponse
    {
        // Extract JWT claims from the authorization header
        $token = $request->bearerToken();

        if ($token === null || $token === '') {
            return response()->json([
                'error' => [
                    'code' => 'TOKEN_INVALID',
                    'message' => 'Token de autorización requerido',
                    'trace_id' => $request->attributes->get('trace_id'),
                ],
            ], 401);
        }

        // Verify the token has the imp claim (it's an impersonation token)
        try {
            $payload = $this->jwtService->decode($token);
        } catch (\Throwable) {
            return response()->json([
                'error' => [
                    'code' => 'TOKEN_INVALID',
                    'message' => 'Token inválido',
                    'trace_id' => $request->attributes->get('trace_id'),
                ],
            ], 401);
        }

        if (! isset($payload['imp']) || ! is_string($payload['imp']) || $payload['imp'] === '') {
            return response()->json([
                'error' => [
                    'code' => 'FORBIDDEN',
                    'message' => 'Esta acción solo puede ejecutarse con un token de suplantación',
                    'trace_id' => $request->attributes->get('trace_id'),
                ],
            ], 403);
        }

        $jti = isset($payload['jti']) && is_string($payload['jti']) ? $payload['jti'] : '';
        $useCase->execute($payload, $request->ip() ?? 'unknown', $jti);

        return response()->json(null, 204);
    }
}
