<?php

declare(strict_types=1);

namespace Urbania\Authorization\Infrastructure\Http\Controllers;

use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Routing\Controller;
use Urbania\Authorization\Application\DTOs\CreateAssignmentRequestDto;
use Urbania\Authorization\Application\UseCases\Assignments\CreateAssignmentUseCase;
use Urbania\Authorization\Application\UseCases\Assignments\RevokeAssignmentUseCase;
use Urbania\Authorization\Infrastructure\Http\Requests\CreateAssignmentRequest;
use Urbania\Authorization\Infrastructure\Http\Resources\AssignmentResource;

final class AssignmentController extends Controller
{
    use HandlesAuthorizationRequest;

    public function store(CreateAssignmentRequest $request, CreateAssignmentUseCase $useCase): JsonResponse
    {
        /** @var array{user_id: string, role_id: string, scope_type: string, scope_id: string, vigencia_inicio?: string|null, vigencia_fin?: string|null} $validated */
        $validated = $request->validated();

        $dto = new CreateAssignmentRequestDto(
            userId: $validated['user_id'],
            roleId: $validated['role_id'],
            scopeType: $validated['scope_type'],
            scopeId: $validated['scope_id'],
            startsAt: $validated['vigencia_inicio'] ?? null,
            endsAt: $validated['vigencia_fin'] ?? null,
            assignedByUserId: $this->actorUserId($request),
        );

        $assignment = $useCase->execute($dto);
        $assignment->load('role');
        $resource = new AssignmentResource($assignment);

        return response()->json([
            'data' => $resource->resolve($request),
            'meta' => ['trace_id' => $request->attributes->get('trace_id')],
        ], 201);
    }

    public function destroy(Request $request, string $id, RevokeAssignmentUseCase $useCase): JsonResponse
    {
        $useCase->execute($id, $this->actorUserId($request));

        return response()->json(null, 204);
    }
}
