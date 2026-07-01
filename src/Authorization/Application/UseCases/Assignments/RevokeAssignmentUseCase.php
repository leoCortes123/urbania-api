<?php

declare(strict_types=1);

namespace Urbania\Authorization\Application\UseCases\Assignments;

use App\Models\RoleAssignment;
use Urbania\Authorization\Application\UseCases\InvalidatesPermissionCache;
use Urbania\Authorization\Application\UseCases\LogsPermissionAudit;
use Urbania\Authorization\Domain\Exceptions\AssignmentNotFoundException;

final readonly class RevokeAssignmentUseCase
{
    use InvalidatesPermissionCache;
    use LogsPermissionAudit;

    public function execute(string $assignmentId, string $actorUserId): void
    {
        $assignment = RoleAssignment::find($assignmentId);

        if ($assignment === null) {
            throw new AssignmentNotFoundException;
        }

        $userId = $assignment->user_id;

        $assignment->delete();

        $this->invalidatePermissionCacheForUser($userId);

        $this->logPermissionAudit($actorUserId, 'revoke', 'role_assignment', 'granted', [
            'assignment_id' => $assignmentId,
            'user_id' => $userId,
        ]);
    }
}
