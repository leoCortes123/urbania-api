<?php

declare(strict_types=1);

namespace Urbania\Auth\Application\UseCases\RegistroResidentes;

use App\Models\Contact as ContactModel;
use App\Models\PropertyOccupant as OccupantModel;
use Directorio\Domain\Repositories\PropertyOccupantRepository;
use Urbania\Auth\Application\DTOs\EnablePortalRequestDto;
use Urbania\Auth\Application\DTOs\EnablePortalResponseDto;
use Urbania\Auth\Domain\Entities\UserEntity;
use Urbania\Auth\Domain\Repositories\UserRepositoryInterface;
use Urbania\Shared\Domain\ValueObjects\Uuid;

final readonly class EnablePortalUseCase
{
    public function __construct(
        private PropertyOccupantRepository $occupantRepository,
        private UserRepositoryInterface $userRepository,
    ) {}

    public function execute(EnablePortalRequestDto $request): EnablePortalResponseDto
    {
        $occupant = $this->occupantRepository->findById($request->occupantId);

        if ($occupant === null) {
            throw new \Urbania\Auth\Domain\Exceptions\UserNotFoundException('El ocupante no existe');
        }

        // Verify the occupant is linked to a unit
        $propertyId = $occupant->propertyId();
        if ($propertyId === '' || $propertyId === '0') {
            throw new \Urbania\Auth\Domain\Exceptions\UserNotFoundException('El ocupante no está vinculado a una unidad');
        }

        // Get contact to find the user_id
        $contactModel = ContactModel::find($occupant->contactId());
        if ($contactModel === null || $contactModel->user_id === null) {
            throw new \Urbania\Auth\Domain\Exceptions\UserNotFoundException('El ocupante no tiene un usuario asociado');
        }

        $userId = $contactModel->user_id;
        $user = $this->userRepository->findById(Uuid::fromString($userId));

        if ($user === null) {
            throw new \Urbania\Auth\Domain\Exceptions\UserNotFoundException('El usuario asociado al ocupante no existe');
        }

        // Idempotent: if already portal primary, just regenerate code
        $isAlreadyPortalPrimary = $occupant->isPortalPrimary();

        // If user is already active, just refresh the code without changing status
        $isAlreadyActive = $user->status() === \Urbania\Auth\Domain\ValueObjects\UserStatus::ACTIVE;

        if (! $isAlreadyPortalPrimary) {
            // Atomic swap in a single transaction
            $this->occupantRepository->setPortalPrimary($propertyId, $request->occupantId);
        }

        if ($isAlreadyActive) {
            // Refresh: regenerate code but keep user active
            $code = random_int(100000, 999999);
            $expiresAt = (new \DateTimeImmutable)->modify('+48 hours');

            // Use reflection to set activation code without changing status
            $this->setActivationCodeDirect($user, (string) $code, $expiresAt);
            $this->userRepository->update($user);

            return new EnablePortalResponseDto(
                activationCode: (string) $code,
                expiresAt: $expiresAt->format('c'),
            );
        }

        // Normal flow: set pending activation
        $code = random_int(100000, 999999);
        $expiresAt = (new \DateTimeImmutable)->modify('+48 hours');

        $user->setPendingActivation((string) $code, $expiresAt);
        $this->userRepository->update($user);

        return new EnablePortalResponseDto(
            activationCode: (string) $code,
            expiresAt: $expiresAt->format('c'),
        );
    }

    /**
     * Directly set activation code on the entity without changing status.
     * Used for refreshing an already-active user's code.
     */
    private function setActivationCodeDirect(UserEntity $user, string $code, \DateTimeImmutable $expiresAt): void
    {
        $reflection = new \ReflectionProperty($user, 'activationCode');
        $reflection->setAccessible(true);
        $reflection->setValue($user, $code);

        $reflection = new \ReflectionProperty($user, 'activationCodeExpiresAt');
        $reflection->setAccessible(true);
        $reflection->setValue($user, $expiresAt);

        $reflection = new \ReflectionProperty($user, 'updatedAt');
        $reflection->setAccessible(true);
        $reflection->setValue($user, new \DateTimeImmutable);
    }
}
