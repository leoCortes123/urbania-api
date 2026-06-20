<?php

declare(strict_types=1);

namespace Database\Factories;

use App\Models\RefreshToken;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Str;

/**
 * @extends Factory<RefreshToken>
 */
class RefreshTokenFactory extends Factory
{
    protected $model = RefreshToken::class;

    /**
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'id' => (string) Str::orderedUuid(),
            'user_id' => User::factory(),
            'session_id' => (string) Str::orderedUuid(),
            'token_hash' => hash('sha256', fake()->uuid()),
            'token_family' => (string) Str::orderedUuid(),
            'previous_token_hash' => null,
            'device_fingerprint' => hash('sha256', fake()->userAgent()),
            'device_name' => fake()->userAgent(),
            'ip_address' => fake()->ipv4(),
            'user_agent' => fake()->userAgent(),
            'expires_at' => now()->addDays(30),
            'revoked_at' => null,
            'revocation_reason' => null,
            'last_used_at' => null,
            'created_at' => now(),
        ];
    }

    public function revoked(string $reason = 'logout'): static
    {
        return $this->state(fn (array $attributes) => [
            'revoked_at' => now(),
            'revocation_reason' => $reason,
        ]);
    }

    public function expired(): static
    {
        return $this->state(fn (array $attributes) => [
            'expires_at' => now()->subDay(),
        ]);
    }

    public function used(): static
    {
        return $this->state(fn (array $attributes) => [
            'last_used_at' => now(),
        ]);
    }
}
