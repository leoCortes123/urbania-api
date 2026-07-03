<?php

declare(strict_types=1);

namespace Urbania\Auth\Infrastructure\Http\Controllers;

use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Routing\Controller;
use Urbania\Auth\Application\DTOs\EnablePortalRequestDto;
use Urbania\Auth\Application\DTOs\ResendActivationRequestDto;
use Urbania\Auth\Application\UseCases\RegistroResidentes\EnablePortalUseCase;
use Urbania\Auth\Application\UseCases\RegistroResidentes\ResendActivationUseCase;

final class AdminResidentController extends Controller
{
    public function enablePortal(string $occupantId, Request $request, EnablePortalUseCase $useCase): JsonResponse
    {
        $dto = new EnablePortalRequestDto(
            occupantId: $occupantId,
        );

        $result = $useCase->execute($dto);

        return response()->json([
            'data' => [
                'activation_code' => $result->activationCode,
                'expires_at' => $result->expiresAt,
            ],
            'meta' => ['trace_id' => $request->attributes->get('trace_id')],
        ], 200);
    }

    public function resendActivation(string $occupantId, Request $request, ResendActivationUseCase $useCase): JsonResponse
    {
        $dto = new ResendActivationRequestDto(
            occupantId: $occupantId,
        );

        $result = $useCase->execute($dto);

        return response()->json([
            'data' => [
                'activation_code' => $result->activationCode,
                'expires_at' => $result->expiresAt,
            ],
            'meta' => ['trace_id' => $request->attributes->get('trace_id')],
        ], 200);
    }
}
