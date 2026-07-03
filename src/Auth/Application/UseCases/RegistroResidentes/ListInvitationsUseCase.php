<?php

declare(strict_types=1);

namespace Urbania\Auth\Application\UseCases\RegistroResidentes;

use App\Models\Contact as ContactModel;
use App\Models\PropertyOccupant as OccupantModel;
use App\Models\OccupantType as OccupantTypeModel;
use Urbania\Auth\Application\DTOs\InvitationListItemDto;
use Urbania\Auth\Domain\Exceptions\NotPortalPrimaryException;
use Urbania\Auth\Domain\Repositories\InvitationRepository;

final readonly class ListInvitationsUseCase
{
    public function __construct(
        private InvitationRepository $invitationRepository,
    ) {}

    /**
     * @return InvitationListItemDto[]
     */
    public function execute(string $userId, ?string $status = null): array
    {
        // Find the portal primary occupant for this user
        $portalPrimary = $this->findPortalPrimaryByUserId($userId);

        if ($portalPrimary === null) {
            throw new NotPortalPrimaryException;
        }

        $propertyId = $portalPrimary->property_id;

        $invitations = $this->invitationRepository->findByProperty($propertyId, $status);

        $result = [];

        foreach ($invitations as $invitation) {
            $occupantType = OccupantTypeModel::find($invitation->occupantTypeId());
            $occupantTypeName = $occupantType !== null ? $occupantType->name : 'Desconocido';

            $result[] = new InvitationListItemDto(
                id: $invitation->id()->toString(),
                inviteeEmail: $invitation->inviteeEmail(),
                inviteeName: $invitation->inviteeName(),
                occupantType: $occupantTypeName,
                status: $invitation->status(),
                createdAt: $invitation->createdAt()->format('c'),
                expiresAt: $invitation->expiresAt()->format('c'),
                acceptedAt: $invitation->acceptedAt() !== null
                    ? $invitation->acceptedAt()->format('c')
                    : null,
            );
        }

        return $result;
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
