<?php

declare(strict_types=1);

namespace Urbania\Auth\Application\UseCases\RegistroResidentes;

use App\Models\User as UserModel;
use App\Models\Property as PropertyModel;
use App\Models\OccupantType as OccupantTypeModel;
use Urbania\Auth\Application\DTOs\VerifyInvitationResponseDto;
use Urbania\Auth\Domain\Exceptions\InvitationAlreadyAcceptedException;
use Urbania\Auth\Domain\Exceptions\InvitationExpiredException;
use Urbania\Auth\Domain\Repositories\InvitationRepository;

final readonly class VerifyInvitationUseCase
{
    public function __construct(
        private InvitationRepository $invitationRepository,
    ) {}

    public function execute(string $token): VerifyInvitationResponseDto
    {
        $invitation = $this->invitationRepository->findByToken($token);

        if ($invitation === null) {
            throw new InvitationExpiredException('La invitación no existe o fue revocada.');
        }

        if ($invitation->isExpired()) {
            throw new InvitationExpiredException;
        }

        if ($invitation->isAccepted()) {
            throw new InvitationAlreadyAcceptedException;
        }

        if ($invitation->isRevoked()) {
            throw new InvitationExpiredException('La invitación ha sido cancelada.');
        }

        // Get inviter name
        $inviter = UserModel::find($invitation->inviterUserId());
        $inviterName = $inviter !== null ? $inviter->name : 'Desconocido';

        // Get property name
        $property = PropertyModel::find($invitation->propertyId());
        $propertyName = $property !== null ? $property->unit_number : 'Unidad desconocida';

        // Get occupant type name
        $occupantType = OccupantTypeModel::find($invitation->occupantTypeId());
        $occupantTypeName = $occupantType !== null ? $occupantType->name : 'Desconocido';

        return new VerifyInvitationResponseDto(
            id: $invitation->id()->toString(),
            inviterName: $inviterName,
            propertyName: $propertyName,
            inviteeEmail: $invitation->inviteeEmail(),
            inviteeName: $invitation->inviteeName(),
            occupantType: $occupantTypeName,
            status: $invitation->status(),
            expiresAt: $invitation->expiresAt()->format('c'),
        );
    }
}
