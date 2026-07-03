<?php

declare(strict_types=1);

namespace Urbania\Auth\Application\UseCases\RegistroResidentes;

use App\Models\Contact as ContactModel;
use App\Models\PropertyOccupant as OccupantModel;
use App\Models\OccupantType as OccupantTypeModel;
use Urbania\Auth\Application\DTOs\RegisterByInvitationRequestDto;
use Urbania\Auth\Application\DTOs\RegisterByInvitationResponseDto;
use Urbania\Auth\Application\Services\JwtServiceInterface;
use Urbania\Auth\Domain\Entities\UserEntity;
use Urbania\Auth\Domain\Exceptions\EmailAlreadyExistsException;
use Urbania\Auth\Domain\Exceptions\InvitationAlreadyAcceptedException;
use Urbania\Auth\Domain\Exceptions\InvitationExpiredException;
use Urbania\Auth\Domain\Repositories\InvitationRepository;
use Urbania\Auth\Domain\Repositories\UserRepositoryInterface;
use Urbania\Auth\Domain\ValueObjects\Password;
use Urbania\Auth\Domain\ValueObjects\SessionId;
use Urbania\Auth\Domain\ValueObjects\UserRole;
use Urbania\Shared\Domain\ValueObjects\Email;

final readonly class RegisterByInvitationUseCase
{
    public function __construct(
        private InvitationRepository $invitationRepository,
        private UserRepositoryInterface $userRepository,
        private JwtServiceInterface $jwtService,
    ) {}

    public function execute(RegisterByInvitationRequestDto $request): RegisterByInvitationResponseDto
    {
        // 1. Validate token de invitación
        $invitation = $this->invitationRepository->findByToken($request->invitationToken);

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

        // 2. Verificar que el email de la invitación no tenga ya un usuario activo
        $email = Email::fromString($invitation->inviteeEmail());
        $existingUser = $this->userRepository->findByEmail($email);

        if ($existingUser !== null) {
            if ($existingUser->deletedAt() === null) {
                throw new EmailAlreadyExistsException;
            }
        }

        // 3. Crear usuario
        $password = Password::fromPlainText($request->password);
        $user = UserEntity::create(
            email: $email,
            name: $request->name,
            password: $password,
            role: UserRole::USER,
            phone: $request->phone,
            organizationId: null,
        );

        $this->userRepository->save($user);

        // 4. Buscar o crear el contacto asociado al email
        $contact = ContactModel::where('email', $invitation->inviteeEmail())->first();

        if ($contact === null) {
            $contact = new ContactModel;
            $contact->email = $invitation->inviteeEmail();
            $contact->full_name = $request->name;
            $contact->phone = $request->phone;
            $contact->user_id = $user->id()->toString();
            $contact->save();
        } else {
            // Link existing contact to the new user
            $contact->user_id = $user->id()->toString();
            $contact->save();
        }

        // 5. Crear el vínculo property_occupants
        $occupant = new OccupantModel;
        $occupant->property_id = $invitation->propertyId();
        $occupant->contact_id = $contact->id;
        $occupant->occupant_type_id = $invitation->occupantTypeId();
        $occupant->is_primary = false;
        $occupant->is_legal_owner = false;
        $occupant->is_portal_primary = false;
        $occupant->is_active = true;
        $occupant->save();

        // 6. Marcar la invitación como accepted
        $invitation->markAsAccepted($user->id()->toString());
        $this->invitationRepository->update($invitation);

        // 7. Generar token JWT
        $sessionId = SessionId::generate();
        $accessToken = $this->jwtService->generateAccessToken(
            userId: $user->id()->toString(),
            role: $user->role()->value,
            mfaVerified: false,
            sessionId: $sessionId,
            deviceFingerprint: '',
            organizationId: $user->organizationId(),
        );

        $refreshToken = $this->jwtService->generateRefreshToken();

        return new RegisterByInvitationResponseDto(
            accessToken: $accessToken->toString(),
            refreshToken: $refreshToken,
            expiresIn: '3600',
        );
    }
}
