<?php

declare(strict_types=1);

namespace Urbania\Auth\Infrastructure\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;
use Urbania\Auth\Application\DTOs\UserResponseDto;

/**
 * @mixin UserResponseDto
 */
final class UserResource extends JsonResource
{
    /**
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        /** @var UserResponseDto $dto */
        $dto = $this->resource;

        return [
            'id' => $dto->id,
            'name' => $dto->name,
            'email' => $dto->email,
            'phone' => $dto->phone,
            'role' => $dto->role,
            'status' => $dto->status,
            'avatar_url' => $dto->avatarUrl,
            'created_at' => $dto->createdAt,
        ];
    }
}
