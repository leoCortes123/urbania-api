<?php

declare(strict_types=1);

namespace Urbania\Auth\Infrastructure\Persistence;

use App\Models\User as UserModel;
use Urbania\Auth\Domain\Entities\UserEntity;
use Urbania\Auth\Domain\Repositories\UserRepositoryInterface;
use Urbania\Auth\Infrastructure\Mappers\UserMapper;
use Urbania\Shared\Domain\ValueObjects\Email;
use Urbania\Shared\Domain\ValueObjects\Uuid;

final readonly class EloquentUserRepository implements UserRepositoryInterface
{
    public function __construct(
        private UserMapper $mapper,
    ) {}

    public function findByEmail(Email $email): ?UserEntity
    {
        $model = UserModel::where('email', $email->toString())->first();

        if ($model === null) {
            return null;
        }

        return $this->mapper->toDomain($model);
    }

    public function findById(Uuid $id): ?UserEntity
    {
        $model = UserModel::find($id->toString());

        if ($model === null) {
            return null;
        }

        return $this->mapper->toDomain($model);
    }

    public function save(UserEntity $user): void
    {
        $data = $this->mapper->toPersistence($user);
        UserModel::create($data);
    }

    public function update(UserEntity $user): void
    {
        $data = $this->mapper->toPersistence($user);
        UserModel::where('id', $user->id()->toString())->update($data);
    }

    public function delete(Uuid $id): void
    {
        UserModel::where('id', $id->toString())->delete();
    }

    public function existsByEmail(Email $email): bool
    {
        return UserModel::where('email', $email->toString())->exists();
    }
}
