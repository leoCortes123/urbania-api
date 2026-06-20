<?php

declare(strict_types=1);

namespace Urbania\Auth\Infrastructure\Persistence;

use Illuminate\Database\Query\Builder;
use Illuminate\Support\Facades\DB;
use Urbania\Auth\Application\Services\PasswordHistoryServiceInterface;
use Urbania\Shared\Domain\ValueObjects\Email;
use Urbania\Shared\Domain\ValueObjects\Uuid;

final readonly class EloquentPasswordHistoryService implements PasswordHistoryServiceInterface
{
    /**
     * @return array<int, string>
     */
    public function getRecent(Email $email, int $limit = 12): array
    {
        $rows = DB::table('password_history')
            ->where('user_id', function ($query) use ($email): void {
                /** @var Builder $query */
                $query->select('id')
                    ->from('users')
                    ->where('email', $email->toString());
            })
            ->orderByDesc('created_at')
            ->limit($limit)
            ->pluck('password_hash');

        /** @var array<int, string> $result */
        $result = $rows->all();

        return $result;
    }

    public function save(Email $email, string $passwordHash): void
    {
        $userId = DB::table('users')
            ->where('email', $email->toString())
            ->value('id');

        if ($userId === null) {
            return;
        }

        DB::table('password_history')->insert([
            'id' => Uuid::v7()->toString(),
            'user_id' => $userId,
            'password_hash' => $passwordHash,
            'created_at' => now(),
        ]);
    }
}
