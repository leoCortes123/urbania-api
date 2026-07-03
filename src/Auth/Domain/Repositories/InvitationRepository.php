<?php

declare(strict_types=1);

namespace Urbania\Auth\Domain\Repositories;

use Urbania\Auth\Domain\Entities\InvitationEntity;
use Urbania\Shared\Domain\ValueObjects\Uuid;

interface InvitationRepository
{
    public function findById(Uuid $id): ?InvitationEntity;

    public function findByToken(string $token): ?InvitationEntity;

    public function findPendingByEmailAndProperty(string $email, string $propertyId): ?InvitationEntity;

    /** @return InvitationEntity[] */
    public function findByProperty(string $propertyId, ?string $status = null): array;

    public function save(InvitationEntity $invitation): void;

    public function update(InvitationEntity $invitation): void;
}
