<?php

declare(strict_types=1);

namespace Urbania\Auth\Domain\Repositories;

use Urbania\Auth\Domain\Entities\UserEntity;
use Urbania\Shared\Domain\ValueObjects\Email;
use Urbania\Shared\Domain\ValueObjects\Uuid;

interface UserRepositoryInterface
{
    public function findByEmail(Email $email): ?UserEntity;

    public function findById(Uuid $id): ?UserEntity;

    public function save(UserEntity $user): void;

    public function update(UserEntity $user): void;

    public function delete(Uuid $id): void;

    public function existsByEmail(Email $email): bool;
}
