<?php

declare(strict_types=1);

namespace Urbania\Auth\Infrastructure\Persistence;

use App\Models\User as UserModel;
use Illuminate\Support\Facades\DB;
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
        $data['organization_id'] = $data['organization_id'] ?? $this->defaultOrganizationId();

        UserModel::create($data);
    }

    public function update(UserEntity $user): void
    {
        if ($user->hasChangedFields()) {
            $data = $this->mapper->toPersistencePartial($user, $user->changedFields());
        } else {
            $data = $this->mapper->toPersistence($user);
            $data['organization_id'] = $data['organization_id'] ?? $this->defaultOrganizationId();
        }

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

    /**
     * Devuelve el id de la primera organización existente.
     * Se usa como tenant por defecto durante la transición a multi-tenancy.
     */
    private function defaultOrganizationId(): ?string
    {
        $organization = DB::table('organizations')->first();

        if (! is_object($organization) || ! property_exists($organization, 'id')) {
            return null;
        }

        $id = $organization->id;

        return is_string($id) ? $id : null;
    }
}
