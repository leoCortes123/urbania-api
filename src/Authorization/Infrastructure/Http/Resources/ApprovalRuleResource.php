<?php

declare(strict_types=1);

namespace Urbania\Authorization\Infrastructure\Http\Resources;

use App\Models\ApprovalRule;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

/**
 * @mixin ApprovalRule
 */
final class ApprovalRuleResource extends JsonResource
{
    /**
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        /** @var ApprovalRule $rule */
        $rule = $this->resource;

        return [
            'id' => $rule->id,
            'resource' => $rule->resource,
            'action' => $rule->action,
            'threshold' => $rule->threshold,
            'approver_role' => $rule->approverRole === null ? null : [
                'id' => $rule->approverRole->id,
                'name' => $rule->approverRole->name,
            ],
            'requires_second_approval' => $rule->requires_second_approval,
        ];
    }
}
