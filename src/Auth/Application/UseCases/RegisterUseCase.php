<?php

declare(strict_types=1);

namespace Urbania\Auth\Application\UseCases;

use Urbania\Auth\Application\DTOs\RegisterRequestDto;
use Urbania\Auth\Application\DTOs\RegisterResponseDto;
use Urbania\Auth\Domain\Entities\UserEntity;
use Urbania\Auth\Domain\Events\UserRegistered;
use Urbania\Auth\Domain\Exceptions\EmailAlreadyExistsException;
use Urbania\Auth\Domain\Exceptions\InvalidCredentialsException;
use Urbania\Auth\Domain\Repositories\UserRepositoryInterface;
use Urbania\Auth\Domain\ValueObjects\Password;
use Urbania\Auth\Domain\ValueObjects\UserRole;
use Urbania\Shared\Application\Bus\EventBusInterface;
use Urbania\Shared\Domain\ValueObjects\Email;

final readonly class RegisterUseCase
{
    public function __construct(
        private UserRepositoryInterface $userRepository,
        private EventBusInterface $eventBus,
    ) {}

    public function execute(RegisterRequestDto $request): RegisterResponseDto
    {
        $email = Email::fromString($request->email);

        if ($this->userRepository->existsByEmail($email)) {
            throw new EmailAlreadyExistsException;
        }

        if ($request->password !== $request->passwordConfirmation) {
            throw InvalidCredentialsException::weakPassword();
        }

        $password = Password::fromPlainText($request->password);

        $user = UserEntity::create(
            email: $email,
            name: $request->name,
            password: $password,
            role: UserRole::USER,
            phone: $request->phone,
        );

        $this->userRepository->save($user);

        $this->eventBus->dispatch(new UserRegistered(
            userId: $user->id()->toString(),
            email: $user->email()->toString(),
            timestamp: new \DateTimeImmutable,
        ));

        return new RegisterResponseDto(
            id: $user->id()->toString(),
            name: $user->name(),
            email: $user->email()->toString(),
            phone: $user->phone(),
            role: $user->role()->value,
            status: $user->status()->value,
            message: 'Registro exitoso. Bienvenido a Urbania.',
        );
    }
}
