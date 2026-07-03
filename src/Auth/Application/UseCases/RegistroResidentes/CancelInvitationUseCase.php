<?php

declare(strict_types=1);

namespace Urbania\Auth\Application\UseCases\RegistroResidentes;

use App\Models\Contact as ContactModel;
use App\Models\PropertyOccupant as OccupantModel;
use Urbania\Auth\Domain\Exceptions\InvitationAlreadyAcceptedException;
use Urbania\Auth\Domain\Exceptions\NotPortalPrimaryException;
use Urbania\Auth\Domain\Exceptions\UserNotFoundException;
use Urbania\Auth\Domain\Repositories\InvitationRepository;
use Urbania\Shared\Domain\ValueObjects\Uuid;

final readonly class CancelInvitationUseCase
{
    public function __construct(
        private InvitationRepository $invitationRepository,
    ) {}

    public function execute(string $invitationId, string $userId): void
    {
        $invitation = $this->invitationRepository->findById(Uuid::fromString($invitationId));

        if ($invitation === null) {
            throw new UserNotFoundException('La invitación no existe.');
        }

        if (! $invitation->isPending()) {
            throw new InvitationAlreadyAcceptedException(
                $invitation->isAccepted()
                    ? 'La invitación ya fue aceptada — no se puede cancelar.'
                    : 'La invitación ya fue revocada — no se puede cancelar de nuevo.',
            );
        }

        // Verify the user is the portal primary of the invitation's unit
        $portalPrimary = $this->findPortalPrimaryByUserId($userId);

        if ($portalPrimary === null || $portalPrimary->property_id !== $invitation->propertyId()) {
            throw new NotPortalPrimaryException;
        }

        $invitation->revoke();
        $this->invitationRepository->update($invitation);
    }

    private function findPortalPrimaryByUserId(string $userId): ?OccupantModel
    {
        $contact = ContactModel::where('user_id', $userId)->first();

        if ($contact === null) {
            return null;
        }

        return OccupantModel::where('contact_id', $contact->id)
            ->where('is_portal_primary', true)
            ->where('is_active', true)
            ->whereNull('deleted_at')
            ->first();
    }
}
