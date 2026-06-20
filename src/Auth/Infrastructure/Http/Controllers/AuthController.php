<?php

declare(strict_types=1);

namespace Urbania\Auth\Infrastructure\Http\Controllers;

use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Routing\Controller;
use Urbania\Auth\Application\DTOs\LoginRequestDto;
use Urbania\Auth\Application\DTOs\LogoutRequestDto;
use Urbania\Auth\Application\DTOs\RefreshTokenRequestDto;
use Urbania\Auth\Application\DTOs\RegisterRequestDto;
use Urbania\Auth\Application\DTOs\UserResponseDto;
use Urbania\Auth\Application\UseCases\GetCurrentUserUseCase;
use Urbania\Auth\Application\UseCases\LoginUseCase;
use Urbania\Auth\Application\UseCases\LogoutUseCase;
use Urbania\Auth\Application\UseCases\RefreshTokenUseCase;
use Urbania\Auth\Application\UseCases\RegisterUseCase;
use Urbania\Auth\Domain\Exceptions\TokenInvalidException;
use Urbania\Auth\Infrastructure\Http\Requests\LoginRequest;
use Urbania\Auth\Infrastructure\Http\Requests\LogoutRequest;
use Urbania\Auth\Infrastructure\Http\Requests\RefreshTokenRequest;
use Urbania\Auth\Infrastructure\Http\Requests\RegisterRequest;
use Urbania\Auth\Infrastructure\Http\Resources\TokenResource;
use Urbania\Auth\Infrastructure\Http\Resources\UserResource;

final class AuthController extends Controller
{
    public function login(LoginRequest $request, LoginUseCase $useCase): JsonResponse
    {
        /** @var string $email */
        $email = $request->validated('email');
        /** @var string $password */
        $password = $request->validated('password');

        $dto = new LoginRequestDto(
            email: $email,
            password: $password,
            userAgent: $request->userAgent(),
            ipAddress: $request->ip(),
            acceptLanguage: $request->header('Accept-Language'),
            deviceName: $request->header('X-Device-Name'),
        );

        $result = $useCase->execute($dto);

        if ($result->status === 'FORCE_PASSWORD_CHANGE') {
            return response()->json([
                'error' => [
                    'code' => 'FORCE_PASSWORD_CHANGE',
                    'message' => 'Debes cambiar tu contraseña antes de continuar',
                    'trace_id' => $request->attributes->get('trace_id'),
                ],
                'data' => [
                    'limited_token' => $result->limitedToken,
                    'token_type' => 'bearer',
                    'expires_in' => 300,
                ],
            ], 403);
        }

        if ($result->status === 'MFA_REQUIRED') {
            return response()->json([
                'error' => [
                    'code' => 'MFA_REQUIRED',
                    'message' => 'Se requiere verificación de autenticación multifactor',
                    'trace_id' => $request->attributes->get('trace_id'),
                ],
            ], 401);
        }

        $resource = new TokenResource($result);

        return response()->json([
            'data' => $resource->resolve($request),
            'meta' => ['trace_id' => $request->attributes->get('trace_id')],
        ], 200);
    }

    public function register(RegisterRequest $request, RegisterUseCase $useCase): JsonResponse
    {
        /** @var string $name */
        $name = $request->validated('name');
        /** @var string $email */
        $email = $request->validated('email');
        /** @var string $password */
        $password = $request->validated('password');
        /** @var string $passwordConfirmation */
        $passwordConfirmation = $request->validated('password_confirmation');
        /** @var string|null $phone */
        $phone = $request->validated('phone');
        /** @var string|null $unit */
        $unit = $request->validated('unit');

        $dto = new RegisterRequestDto(
            name: $name,
            email: $email,
            password: $password,
            passwordConfirmation: $passwordConfirmation,
            phone: $phone,
            unit: $unit,
        );

        $result = $useCase->execute($dto);

        $userDto = new UserResponseDto(
            id: $result->id,
            name: $result->name,
            email: $result->email,
            phone: $result->phone,
            unit: $result->unit,
            role: $result->role,
            status: $result->status,
            avatarUrl: null,
            createdAt: null,
        );

        $resource = new UserResource($userDto);
        $data = $resource->resolve($request);
        $data['message'] = $result->message;

        return response()->json([
            'data' => $data,
            'meta' => ['trace_id' => $request->attributes->get('trace_id')],
        ], 201);
    }

    public function logout(LogoutRequest $request, LogoutUseCase $useCase): JsonResponse
    {
        /** @var string $refreshToken */
        $refreshToken = $request->validated('refresh_token');

        $dto = new LogoutRequestDto(
            refreshToken: $refreshToken,
        );

        $useCase->execute($dto);

        return response()->json(null, 204);
    }

    public function refresh(RefreshTokenRequest $request, RefreshTokenUseCase $useCase): JsonResponse
    {
        /** @var string $refreshToken */
        $refreshToken = $request->validated('refresh_token');

        $dto = new RefreshTokenRequestDto(
            refreshToken: $refreshToken,
            userAgent: $request->userAgent(),
            ipAddress: $request->ip(),
        );

        $result = $useCase->execute($dto);

        $resource = new TokenResource($result);

        return response()->json([
            'data' => $resource->resolve($request),
            'meta' => ['trace_id' => $request->attributes->get('trace_id')],
        ], 200);
    }

    public function me(Request $request, GetCurrentUserUseCase $useCase): JsonResponse
    {
        $bearerToken = $request->bearerToken();

        if ($bearerToken === null || $bearerToken === '') {
            throw new TokenInvalidException('Token is required');
        }

        $result = $useCase->execute($bearerToken);

        $resource = new UserResource($result);

        return response()->json([
            'data' => $resource->resolve($request),
            'meta' => ['trace_id' => $request->attributes->get('trace_id')],
        ], 200);
    }
}
