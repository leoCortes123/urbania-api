<?php

declare(strict_types=1);

namespace Urbania\Auth\Application\UseCases\RegistroResidentes;

use App\Models\Contact as ContactModel;
use Directorio\Domain\Repositories\PropertyOccupantRepository;
use Urbania\Auth\Application\DTOs\ResendActivationRequestDto;
use Urbania\Auth\Application\DTOs\ResendActivationResponseDto;
use Urbania\Auth\Domain\Exceptions\AccountAlreadyActiveException;
use Urbania\Auth\Domain\Repositories\UserRepositoryInterface;
use Urbania\Shared\Domain\ValueObjects\Uuid;

final readonly class ResendActivationUseCase
{
    public function __construct(
        private PropertyOccupantRepository $occupantRepository,
        private UserRepositoryInterface $userRepository,
    ) {}

    public function execute(ResendActivationRequestDto $request): ResendActivationResponseDto
    {
        $occupant = $this->occupantRepository->findById($request->occupantId);

        if ($occupant === null) {
            throw new \Urbania\Auth\Domain\Exceptions\UserNotFoundException('El ocupante no existe');
        }

        $contactModel = ContactModel::find($occupant->contactId());
        if ($contactModel === null || $contactModel->user_id === null) {
            throw new \Urbania\Auth\Domain\Exceptions\UserNotFoundException('El ocupante no tiene un usuario asociado');
        }

        $user = $this->userRepository->findById(Uuid::fromString($contactModel->user_id));

        if ($user === null) {
            throw new \Urbania\Auth\Domain\Exceptions\UserNotFoundException('El usuario asociado al ocupante no existe');
        }

        if ($user->status() === \Urbania\Auth\Domain\ValueObjects\UserStatus::ACTIVE) {
            throw new AccountAlreadyActiveException;
        }

        $code = random_int(100000, 999999);
        $expiresAt = (new \DateTimeImmutable)->modify('+48 hours');

        $user->setPendingActivation((string) $code, $expiresAt);
        $this->userRepository->update($user);

        return new ResendActivationResponseDto(
            activationCode: (string) $code,
            expiresAt: $expiresAt->format('c'),
        );
    }
}
