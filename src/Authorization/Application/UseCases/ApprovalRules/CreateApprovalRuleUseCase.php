<?php

declare(strict_types=1);

namespace Urbania\Authorization\Application\UseCases\ApprovalRules;

use App\Models\ApprovalRule;
use App\Models\Role;
use Ramsey\Uuid\Uuid;
use Urbania\Authorization\Application\DTOs\CreateApprovalRuleRequestDto;
use Urbania\Authorization\Application\UseCases\LogsPermissionAudit;
use Urbania\Authorization\Domain\Exceptions\ApprovalRuleInvalidApproverException;

final readonly class CreateApprovalRuleUseCase
{
    use LogsPermissionAudit;

    public function execute(CreateApprovalRuleRequestDto $dto, string $actorUserId): ApprovalRule
    {
        $approverRole = Role::where('id', $dto->approverRoleId)
            ->where(static function ($query) use ($dto): void {
                $query->where('organization_id', $dto->organizationId)
                    ->orWhere('is_system', true);
            })
            ->first();

        if ($approverRole === null) {
            throw new ApprovalRuleInvalidApproverException;
        }

        $rule = ApprovalRule::create([
            'id' => Uuid::uuid7()->toString(),
            'resource' => $dto->resource,
            'action' => $dto->action,
            'organization_id' => $dto->organizationId,
            'threshold' => $dto->threshold,
            'approver_role_id' => $dto->approverRoleId,
            'requires_second_approval' => $dto->requiresSecondApproval,
        ]);

        $this->logPermissionAudit($actorUserId, 'create_approval_rule', $dto->resource, 'granted', [
            'rule_id' => $rule->id,
            'approver_role_id' => $dto->approverRoleId,
            'threshold' => $dto->threshold,
        ]);

        return $rule;
    }
}
