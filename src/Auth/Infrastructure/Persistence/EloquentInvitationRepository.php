<?php

declare(strict_types=1);

namespace Urbania\Auth\Infrastructure\Persistence;

use App\Models\Invitation as InvitationModel;
use Carbon\Carbon;
use Urbania\Auth\Domain\Entities\InvitationEntity;
use Urbania\Auth\Domain\Repositories\InvitationRepository;
use Urbania\Shared\Domain\ValueObjects\Uuid;

final readonly class EloquentInvitationRepository implements InvitationRepository
{
    public function findById(Uuid $id): ?InvitationEntity
    {
        $model = InvitationModel::find($id->toString());

        if ($model === null) {
            return null;
        }

        return $this->toEntity($model);
    }

    public function findByToken(string $token): ?InvitationEntity
    {
        $model = InvitationModel::where('token', $token)->first();

        if ($model === null) {
            return null;
        }

        return $this->toEntity($model);
    }

    public function findPendingByEmailAndProperty(string $email, string $propertyId): ?InvitationEntity
    {
        $model = InvitationModel::where('invitee_email', $email)
            ->where('property_id', $propertyId)
            ->where('status', 'pending')
            ->first();

        if ($model === null) {
            return null;
        }

        return $this->toEntity($model);
    }

    public function findByProperty(string $propertyId, ?string $status = null): array
    {
        $query = InvitationModel::where('property_id', $propertyId);

        if ($status !== null) {
            $query->where('status', $status);
        }

        return $query->orderBy('created_at', 'desc')
            ->get()
            ->map(fn (InvitationModel $model) => $this->toEntity($model))
            ->all();
    }

    public function save(InvitationEntity $invitation): void
    {
        $model = new InvitationModel;
        $this->fillModel($model, $invitation);
        $model->save();
    }

    public function update(InvitationEntity $invitation): void
    {
        $model = InvitationModel::find($invitation->id()->toString());

        if ($model === null) {
            return;
        }

        $this->fillModel($model, $invitation);
        $model->save();
    }

    private function fillModel(InvitationModel $model, InvitationEntity $invitation): void
    {
        $model->id = $invitation->id()->toString();
        $model->token = $invitation->token();
        $model->inviter_user_id = $invitation->inviterUserId();
        $model->property_id = $invitation->propertyId();
        $model->invitee_email = $invitation->inviteeEmail();
        $model->invitee_name = $invitation->inviteeName();
        $model->occupant_type_id = $invitation->occupantTypeId();
        $model->status = $invitation->status();
        $model->expires_at = Carbon::instance($invitation->expiresAt());
        $model->accepted_at = $invitation->acceptedAt() !== null
            ? Carbon::instance($invitation->acceptedAt())
            : null;
        $model->accepted_by_user_id = $invitation->acceptedByUserId();
        $model->revoked_at = $invitation->revokedAt() !== null
            ? Carbon::instance($invitation->revokedAt())
            : null;
    }

    private function toEntity(InvitationModel $model): InvitationEntity
    {
        /** @var \DateTime $expiresAt */
        $expiresAt = $model->expires_at;

        /** @var \DateTime|null $acceptedAt */
        $acceptedAt = $model->accepted_at;

        /** @var \DateTime|null $revokedAt */
        $revokedAt = $model->revoked_at;

        /** @var \DateTime $createdAt */
        $createdAt = $model->created_at;

        /** @var \DateTime $updatedAt */
        $updatedAt = $model->updated_at;

        return InvitationEntity::reconstitute(
            id: $model->id,
            token: $model->token,
            inviterUserId: $model->inviter_user_id,
            propertyId: $model->property_id,
            inviteeEmail: $model->invitee_email,
            inviteeName: $model->invitee_name,
            occupantTypeId: $model->occupant_type_id,
            status: $model->status,
            expiresAt: \DateTimeImmutable::createFromMutable($expiresAt),
            createdAt: \DateTimeImmutable::createFromMutable($createdAt),
            updatedAt: \DateTimeImmutable::createFromMutable($updatedAt),
            acceptedAt: $acceptedAt !== null
                ? \DateTimeImmutable::createFromMutable($acceptedAt)
                : null,
            acceptedByUserId: $model->accepted_by_user_id,
            revokedAt: $revokedAt !== null
                ? \DateTimeImmutable::createFromMutable($revokedAt)
                : null,
        );
    }
}
