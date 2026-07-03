<?php

declare(strict_types=1);

namespace Urbania\Auth\Application\UseCases\RegistroResidentes;

use App\Models\Contact as ContactModel;
use App\Models\PropertyOccupant as OccupantModel;
use App\Models\User as UserModel;
use Urbania\Auth\Application\DTOs\UnitOccupantDto;
use Urbania\Auth\Domain\Exceptions\NotPortalPrimaryException;

final readonly class ListUnitOccupantsUseCase
{
    /**
     * @return UnitOccupantDto[]
     */
    public function execute(string $userId): array
    {
        // Find the portal primary occupant for this user
        $portalPrimary = $this->findPortalPrimaryByUserId($userId);

        if ($portalPrimary === null) {
            throw new NotPortalPrimaryException;
        }

        $propertyId = $portalPrimary->property_id;

        // Get all active occupants for this property
        $occupants = OccupantModel::with(['contact', 'occupantType'])
            ->where('property_id', $propertyId)
            ->where('is_active', true)
            ->whereNull('deleted_at')
            ->get();

        $result = [];

        foreach ($occupants as $occupant) {
            $contact = $occupant->contact;
            $occupantType = $occupant->occupantType;

            $userStatus = null;
            if ($contact !== null && $contact->user_id !== null) {
                $user = UserModel::find($contact->user_id);
                $userStatus = $user !== null ? $user->status : null;
            }

            $result[] = new UnitOccupantDto(
                occupantId: $occupant->id,
                contactId: $occupant->contact_id,
                userId: $contact !== null ? $contact->user_id : null,
                fullName: $contact !== null ? $contact->full_name : 'Sin nombre',
                email: $contact !== null ? ($contact->email ?? '') : '',
                phone: $contact !== null ? $contact->phone : null,
                occupantType: $occupantType !== null ? $occupantType->name : 'Desconocido',
                isLegalOwner: (bool) $occupant->is_legal_owner,
                isPortalPrimary: (bool) $occupant->is_portal_primary,
                isPrimary: (bool) $occupant->is_primary,
                userStatus: $userStatus,
                isActive: (bool) $occupant->is_active,
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
