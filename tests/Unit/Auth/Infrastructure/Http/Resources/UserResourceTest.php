<?php

declare(strict_types=1);

use Illuminate\Http\Request;
use Urbania\Auth\Application\DTOs\UserResponseDto;
use Urbania\Auth\Infrastructure\Http\Resources\UserResource;

it('formats user response with all fields including nulls', function (): void {
    $dto = new UserResponseDto(
        id: '550e8400-e29b-41d4-a716-446655440000',
        name: 'Juan Perez',
        email: 'juan@example.com',
        phone: null,
        unit: null,
        role: 'user',
        status: 'active',
        avatarUrl: null,
        createdAt: '2026-06-19T12:00:00+00:00',
    );

    $resource = new UserResource($dto);
    $data = $resource->resolve(new Request);

    expect($data)->toBe([
        'id' => '550e8400-e29b-41d4-a716-446655440000',
        'name' => 'Juan Perez',
        'email' => 'juan@example.com',
        'phone' => null,
        'unit' => null,
        'role' => 'user',
        'status' => 'active',
        'avatar_url' => null,
        'created_at' => '2026-06-19T12:00:00+00:00',
    ]);
});

it('formats user response with optional fields populated', function (): void {
    $dto = new UserResponseDto(
        id: '550e8400-e29b-41d4-a716-446655440001',
        name: 'Maria Lopez',
        email: 'maria@example.com',
        phone: '3001234567',
        unit: 'Apto 205',
        role: 'admin',
        status: 'active',
        avatarUrl: 'https://api.urbania.com/avatars/1.jpg',
        createdAt: '2026-06-18T10:00:00+00:00',
    );

    $resource = new UserResource($dto);
    $data = $resource->resolve(new Request);

    expect($data)->toMatchArray([
        'phone' => '3001234567',
        'unit' => 'Apto 205',
        'avatar_url' => 'https://api.urbania.com/avatars/1.jpg',
    ]);
});
