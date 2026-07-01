<?php

declare(strict_types=1);

namespace Urbania\Authorization\Infrastructure\Http\Controllers;

use Illuminate\Http\JsonResponse;
use Illuminate\Routing\Controller;
use Urbania\Authorization\Application\DTOs\CreateApprovalRuleRequestDto;
use Urbania\Authorization\Application\UseCases\ApprovalRules\CreateApprovalRuleUseCase;
use Urbania\Authorization\Infrastructure\Http\Requests\CreateApprovalRuleRequest;
use Urbania\Authorization\Infrastructure\Http\Resources\ApprovalRuleResource;

final class ApprovalRuleController extends Controller
{
    use HandlesAuthorizationRequest;

    public function store(CreateApprovalRuleRequest $request, CreateApprovalRuleUseCase $useCase): JsonResponse
    {
        /** @var array{resource: string, action: string, threshold?: numeric|null, approver_role_id: string, requires_second_approval?: bool} $validated */
        $validated = $request->validated();

        $threshold = isset($validated['threshold']) ? (float) $validated['threshold'] : null;

        $dto = new CreateApprovalRuleRequestDto(
            resource: $validated['resource'],
            action: $validated['action'],
            organizationId: $this->organizationId($request),
            threshold: $threshold,
            approverRoleId: $validated['approver_role_id'],
            requiresSecondApproval: (bool) ($validated['requires_second_approval'] ?? false),
        );

        $rule = $useCase->execute($dto, $this->actorUserId($request));
        $rule->load('approverRole');
        $resource = new ApprovalRuleResource($rule);

        return response()->json([
            'data' => $resource->resolve($request),
            'meta' => ['trace_id' => $request->attributes->get('trace_id')],
        ], 201);
    }
}
