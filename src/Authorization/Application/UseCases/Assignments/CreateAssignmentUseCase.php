<?php

declare(strict_types=1);

namespace Urbania\Authorization\Application\UseCases\Assignments;

use App\Models\Role;
use App\Models\RoleAssignment;
use Ramsey\Uuid\Uuid;
use Urbania\Authorization\Application\DTOs\CreateAssignmentRequestDto;
use Urbania\Authorization\Application\UseCases\InvalidatesPermissionCache;
use Urbania\Authorization\Application\UseCases\LogsPermissionAudit;
use Urbania\Authorization\Domain\Exceptions\AssignmentAlreadyExistsException;
use Urbania\Authorization\Domain\Exceptions\RoleNotFoundException;

final readonly class CreateAssignmentUseCase
{
    use InvalidatesPermissionCache;
    use LogsPermissionAudit;

    public function execute(CreateAssignmentRequestDto $dto): RoleAssignment
    {
        $role = Role::find($dto->roleId);

        if ($role === null) {
            throw new RoleNotFoundException;
        }

        $this->validateDuplicate($dto);

        $assignment = RoleAssignment::create([
            'id' => Uuid::uuid7()->toString(),
            'user_id' => $dto->userId,
            'role_id' => $dto->roleId,
            'scope_type' => $dto->scopeType,
            'scope_id' => $dto->scopeId,
            'starts_at' => $dto->startsAt,
            'ends_at' => $dto->endsAt,
            'assigned_by_user_id' => $dto->assignedByUserId,
        ]);

        $this->invalidatePermissionCacheForUser($dto->userId);

        $this->logPermissionAudit($dto->assignedByUserId, 'grant', 'role_assignment', 'granted', [
            'assignment_id' => $assignment->id,
            'user_id' => $dto->userId,
            'role_id' => $dto->roleId,
            'scope_type' => $dto->scopeType,
            'scope_id' => $dto->scopeId,
        ]);

        return $assignment;
    }

    private function validateDuplicate(CreateAssignmentRequestDto $dto): void
    {
        $exists = RoleAssignment::where('user_id', $dto->userId)
            ->where('role_id', $dto->roleId)
            ->where('scope_type', $dto->scopeType)
            ->where('scope_id', $dto->scopeId)
            ->whereNull('deleted_at')
            ->where(static function ($query): void {
                $query->whereNull('ends_at')
                    ->orWhere('ends_at', '>', now());
            })
            ->exists();

        if ($exists) {
            throw new AssignmentAlreadyExistsException;
        }
    }
}
