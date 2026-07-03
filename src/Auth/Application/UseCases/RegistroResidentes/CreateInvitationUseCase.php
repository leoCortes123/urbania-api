<?php

declare(strict_types=1);

namespace Urbania\Auth\Application\UseCases\RegistroResidentes;

use App\Models\Contact as ContactModel;
use App\Models\PropertyOccupant as OccupantModel;
use App\Models\OccupantType as OccupantTypeModel;
use Urbania\Auth\Application\DTOs\CreateInvitationRequestDto;
use Urbania\Auth\Application\DTOs\CreateInvitationResponseDto;
use Urbania\Auth\Domain\Entities\InvitationEntity;
use Urbania\Auth\Domain\Exceptions\InvitationAlreadyPendingException;
use Urbania\Auth\Domain\Exceptions\InvalidOccupantTypeException;
use Urbania\Auth\Domain\Exceptions\NotPortalPrimaryException;
use Urbania\Auth\Domain\Repositories\InvitationRepository;

final readonly class CreateInvitationUseCase
{
    public function __construct(
        private InvitationRepository $invitationRepository,
    ) {}

    public function execute(CreateInvitationRequestDto $request): CreateInvitationResponseDto
    {
        // Find the portal primary occupant for this user
        $portalPrimary = $this->findPortalPrimaryByUserId($request->inviterUserId);

        if ($portalPrimary === null) {
            throw new NotPortalPrimaryException;
        }

        $propertyId = $portalPrimary->property_id;

        // Validate occupant type exists
        $occupantType = OccupantTypeModel::where('id', $request->occupantTypeId)
            ->where('is_active', true)
            ->first();

        if ($occupantType === null) {
            throw new InvalidOccupantTypeException;
        }

        // Check for existing pending invitation for same email and property
        $existing = $this->invitationRepository->findPendingByEmailAndProperty(
            $request->inviteeEmail,
            $propertyId,
        );

        if ($existing !== null) {
            throw new InvitationAlreadyPendingException;
        }

        // Create invitation
        $invitation = InvitationEntity::create(
            inviterUserId: $request->inviterUserId,
            propertyId: $propertyId,
            inviteeEmail: $request->inviteeEmail,
            inviteeName: $request->inviteeName,
            occupantTypeId: $request->occupantTypeId,
        );

        $this->invitationRepository->save($invitation);

        return new CreateInvitationResponseDto(
            id: $invitation->id()->toString(),
            token: $invitation->token(),
            expiresAt: $invitation->expiresAt()->format('c'),
        );
    }

    /**
     * Encuentra el property_occupant donde el usuario es portal_primary.
     */
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
