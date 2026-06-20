<?php

declare(strict_types=1);

namespace Urbania\Auth\Infrastructure\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;
use Urbania\Auth\Application\DTOs\LoginResponseDto;
use Urbania\Auth\Application\DTOs\TokenResponseDto;

/**
 * @mixin TokenResponseDto|LoginResponseDto
 */
final class TokenResource extends JsonResource
{
    /**
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        /** @var TokenResponseDto|LoginResponseDto $dto */
        $dto = $this->resource;

        $data = [
            'access_token' => $dto->accessToken,
            'refresh_token' => $dto->refreshToken,
            'token_type' => $dto->tokenType,
            'expires_in' => $dto->expiresIn,
        ];

        if ($dto instanceof LoginResponseDto) {
            $data['user'] = new UserResource($dto->user);
        }

        return $data;
    }
}
