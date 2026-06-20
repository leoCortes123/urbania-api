<?php

declare(strict_types=1);

namespace Urbania\Auth\Infrastructure\Persistence;

use Illuminate\Support\Facades\DB;
use Urbania\Auth\Domain\Repositories\PasswordResetTokenRepositoryInterface;

final readonly class EloquentPasswordResetTokenRepository implements PasswordResetTokenRepositoryInterface
{
    public function save(string $email, string $tokenHash): void
    {
        DB::table('password_reset_tokens')->updateOrInsert(
            ['email' => $email],
            [
                'token' => $tokenHash,
                'created_at' => now(),
            ]
        );
    }

    /**
     * @return array{token: string, created_at: \DateTimeImmutable}|null
     */
    public function findByEmail(string $email): ?array
    {
        $row = DB::table('password_reset_tokens')
            ->where('email', $email)
            ->first();

        if ($row === null) {
            return null;
        }

        $createdAt = $row->created_at;
        assert(is_string($createdAt) || $createdAt === null);

        $createdAtImmutable = $createdAt !== null
            ? \DateTimeImmutable::createFromFormat('Y-m-d H:i:s', $createdAt)
            : new \DateTimeImmutable;

        if ($createdAtImmutable === false) {
            $createdAtImmutable = new \DateTimeImmutable;
        }

        $token = $row->token;
        assert(is_string($token));

        return [
            'token' => $token,
            'created_at' => $createdAtImmutable,
        ];
    }

    public function delete(string $email): void
    {
        DB::table('password_reset_tokens')->where('email', $email)->delete();
    }
}
