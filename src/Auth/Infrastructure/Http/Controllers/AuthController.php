<?php

declare(strict_types=1);

namespace Urbania\Auth\Infrastructure\Http\Controllers;

use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Routing\Controller;
use Urbania\Auth\Application\DTOs\ChangePasswordRequestDto;
use Urbania\Auth\Application\DTOs\ForgotPasswordRequestDto;
use Urbania\Auth\Application\DTOs\LoginRequestDto;
use Urbania\Auth\Application\DTOs\LogoutRequestDto;
use Urbania\Auth\Application\DTOs\RefreshTokenRequestDto;
use Urbania\Auth\Application\DTOs\RegisterRequestDto;
use Urbania\Auth\Application\DTOs\ResetPasswordRequestDto;
use Urbania\Auth\Application\DTOs\UpdateProfileRequestDto;
use Urbania\Auth\Application\DTOs\UserResponseDto;
use Urbania\Auth\Application\Services\JwtServiceInterface;
use Urbania\Auth\Application\UseCases\ChangePasswordUseCase;
use Urbania\Auth\Application\UseCases\ForgotPasswordUseCase;
use Urbania\Auth\Application\UseCases\GetCurrentUserUseCase;
use Urbania\Auth\Application\UseCases\ListSessionsUseCase;
use Urbania\Auth\Application\UseCases\LoginUseCase;
use Urbania\Auth\Application\UseCases\LogoutUseCase;
use Urbania\Auth\Application\UseCases\MfaDisableUseCase;
use Urbania\Auth\Application\UseCases\MfaEnableUseCase;
use Urbania\Auth\Application\UseCases\MfaRegenerateBackupUseCase;
use Urbania\Auth\Application\UseCases\MfaSetupUseCase;
use Urbania\Auth\Application\UseCases\MfaVerifyBackupUseCase;
use Urbania\Auth\Application\UseCases\MfaVerifyUseCase;
use Urbania\Auth\Application\UseCases\RefreshTokenUseCase;
use Urbania\Auth\Application\UseCases\RegisterUseCase;
use Urbania\Auth\Application\UseCases\ResendVerificationUseCase;
use Urbania\Auth\Application\UseCases\ResetPasswordUseCase;
use Urbania\Auth\Application\UseCases\RevokeAllSessionsUseCase;
use Urbania\Auth\Application\UseCases\RevokeSessionUseCase;
use Urbania\Auth\Application\UseCases\UpdateProfileUseCase;
use Urbania\Auth\Application\UseCases\VerifyEmailUseCase;
use Urbania\Auth\Domain\Exceptions\TokenInvalidException;
use Urbania\Auth\Infrastructure\Http\Requests\ChangePasswordRequest;
use Urbania\Auth\Infrastructure\Http\Requests\ForgotPasswordRequest;
use Urbania\Auth\Infrastructure\Http\Requests\LoginRequest;
use Urbania\Auth\Infrastructure\Http\Requests\LogoutRequest;
use Urbania\Auth\Infrastructure\Http\Requests\MfaDisableRequest;
use Urbania\Auth\Infrastructure\Http\Requests\MfaEnableRequest;
use Urbania\Auth\Infrastructure\Http\Requests\MfaVerifyRequest;
use Urbania\Auth\Infrastructure\Http\Requests\RefreshTokenRequest;
use Urbania\Auth\Infrastructure\Http\Requests\RegisterRequest;
use Urbania\Auth\Infrastructure\Http\Requests\ResetPasswordRequest;
use Urbania\Auth\Infrastructure\Http\Requests\UpdateProfileRequest;
use Urbania\Auth\Infrastructure\Http\Requests\VerifyEmailRequest;
use Urbania\Auth\Infrastructure\Http\Resources\TokenResource;
use Urbania\Auth\Infrastructure\Http\Resources\UserResource;

final class AuthController extends Controller
{
    public function __construct(
        private readonly JwtServiceInterface $jwtService,
    ) {}

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
                'data' => [
                    'limited_token' => $result->limitedToken,
                    'token_type' => 'bearer',
                    'expires_in' => 300,
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

        $dto = new RegisterRequestDto(
            name: $name,
            email: $email,
            password: $password,
            passwordConfirmation: $passwordConfirmation,
            phone: $phone,
        );

        $result = $useCase->execute($dto);

        $userDto = new UserResponseDto(
            id: $result->id,
            name: $result->name,
            email: $result->email,
            phone: $result->phone,
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

        $accessToken = $request->bearerToken();

        if ($accessToken !== null && $accessToken !== '') {
            $payload = $this->jwtService->decode($accessToken);

            if (isset($payload['jti']) && is_string($payload['jti']) && $payload['jti'] !== '') {
                $this->jwtService->revoke($payload['jti']);
            }
        }

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

    public function mfaSetup(Request $request, MfaSetupUseCase $useCase): JsonResponse
    {
        $userId = $request->attributes->get('auth_user_id');
        assert(is_string($userId) && $userId !== '');

        $result = $useCase->execute($userId);

        return response()->json([
            'data' => [
                'secret' => $result->secret,
                'qr_code_url' => $result->qrCodeUrl,
                'backup_codes' => $result->backupCodes,
            ],
            'meta' => ['trace_id' => $request->attributes->get('trace_id')],
        ], 200);
    }

    public function mfaVerify(MfaVerifyRequest $request, MfaVerifyUseCase $useCase): JsonResponse
    {
        /** @var string $mfaToken */
        $mfaToken = $request->validated('mfa_token');
        /** @var string $code */
        $code = $request->validated('code');

        $result = $useCase->execute(
            mfaToken: $mfaToken,
            code: $code,
            userAgent: $request->userAgent() ?? '',
            ipAddress: $request->ip() ?? '',
        );

        $resource = new TokenResource($result);

        return response()->json([
            'data' => $resource->resolve($request),
            'meta' => ['trace_id' => $request->attributes->get('trace_id')],
        ], 200);
    }

    public function mfaVerifyBackup(MfaVerifyRequest $request, MfaVerifyBackupUseCase $useCase): JsonResponse
    {
        /** @var string $mfaToken */
        $mfaToken = $request->validated('mfa_token');
        /** @var string $code */
        $code = $request->validated('code');

        $result = $useCase->execute(
            mfaToken: $mfaToken,
            code: $code,
            userAgent: $request->userAgent() ?? '',
            ipAddress: $request->ip() ?? '',
        );

        $resource = new TokenResource($result);

        return response()->json([
            'data' => $resource->resolve($request),
            'meta' => ['trace_id' => $request->attributes->get('trace_id')],
        ], 200);
    }

    public function mfaEnable(MfaEnableRequest $request, MfaEnableUseCase $useCase): JsonResponse
    {
        $userId = $request->attributes->get('auth_user_id');
        assert(is_string($userId) && $userId !== '');

        /** @var string $code */
        $code = $request->validated('code');

        $useCase->execute(
            userId: $userId,
            code: $code,
            ipAddress: $request->ip() ?? '',
        );

        return response()->json([
            'data' => ['message' => 'MFA habilitado exitosamente'],
            'meta' => ['trace_id' => $request->attributes->get('trace_id')],
        ], 200);
    }

    public function mfaDisable(MfaDisableRequest $request, MfaDisableUseCase $useCase): JsonResponse
    {
        $userId = $request->attributes->get('auth_user_id');
        $currentSessionId = $request->attributes->get('auth_session_id');
        assert(is_string($userId) && $userId !== '');
        assert(is_string($currentSessionId) && $currentSessionId !== '');

        /** @var string $password */
        $password = $request->validated('password');
        /** @var string $code */
        $code = $request->validated('code');

        $useCase->execute(
            userId: $userId,
            password: $password,
            code: $code,
            ipAddress: $request->ip() ?? '',
            currentSessionId: $currentSessionId,
        );

        return response()->json([
            'data' => ['message' => 'MFA deshabilitado exitosamente'],
            'meta' => ['trace_id' => $request->attributes->get('trace_id')],
        ], 200);
    }

    public function mfaRegenerateBackupCodes(Request $request, MfaRegenerateBackupUseCase $useCase): JsonResponse
    {
        $userId = $request->attributes->get('auth_user_id');
        assert(is_string($userId) && $userId !== '');

        $codes = $useCase->execute($userId);

        return response()->json([
            'data' => ['backup_codes' => $codes],
            'meta' => ['trace_id' => $request->attributes->get('trace_id')],
        ], 200);
    }

    public function listSessions(Request $request, ListSessionsUseCase $useCase): JsonResponse
    {
        $userId = $request->attributes->get('auth_user_id');
        $currentSessionId = $request->attributes->get('auth_session_id');
        assert(is_string($userId) && $userId !== '');
        assert(is_string($currentSessionId) && $currentSessionId !== '');

        $sessions = $useCase->execute(
            userId: $userId,
            currentSessionId: $currentSessionId,
        );

        return response()->json([
            'data' => ['sessions' => array_map(fn ($dto) => [
                'session_id' => $dto->sessionId,
                'device_name' => $dto->deviceName,
                'device_fingerprint' => $dto->deviceFingerprint,
                'ip_address' => $dto->ipAddress,
                'last_used_at' => $dto->lastUsedAt,
                'created_at' => $dto->createdAt,
                'is_current' => $dto->isCurrent,
            ], $sessions)],
            'meta' => ['trace_id' => $request->attributes->get('trace_id')],
        ], 200);
    }

    public function revokeAllSessions(Request $request, RevokeAllSessionsUseCase $useCase): JsonResponse
    {
        $userId = $request->attributes->get('auth_user_id');
        $currentSessionId = $request->attributes->get('auth_session_id');
        assert(is_string($userId) && $userId !== '');
        assert(is_string($currentSessionId) && $currentSessionId !== '');

        $useCase->execute(
            userId: $userId,
            currentSessionId: $currentSessionId,
        );

        return response()->json(null, 204);
    }

    public function revokeSession(string $sessionId, Request $request, RevokeSessionUseCase $useCase): JsonResponse
    {
        $userId = $request->attributes->get('auth_user_id');
        assert(is_string($userId) && $userId !== '');

        $useCase->execute(
            userId: $userId,
            sessionId: $sessionId,
        );

        return response()->json(null, 204);
    }

    public function forgotPassword(ForgotPasswordRequest $request, ForgotPasswordUseCase $useCase): JsonResponse
    {
        /** @var string $email */
        $email = $request->validated('email');

        $dto = new ForgotPasswordRequestDto(email: $email);
        $result = $useCase->execute($dto);

        return response()->json([
            'data' => ['message' => $result['message']],
            'meta' => ['trace_id' => $request->attributes->get('trace_id')],
        ], 200);
    }

    public function resetPassword(ResetPasswordRequest $request, ResetPasswordUseCase $useCase): JsonResponse
    {
        /** @var string $email */
        $email = $request->validated('email');
        /** @var string $token */
        $token = $request->validated('token');
        /** @var string $password */
        $password = $request->validated('password');
        /** @var string $passwordConfirmation */
        $passwordConfirmation = $request->validated('password_confirmation');

        $dto = new ResetPasswordRequestDto(
            email: $email,
            token: $token,
            password: $password,
            passwordConfirmation: $passwordConfirmation,
        );

        $result = $useCase->execute($dto);

        return response()->json([
            'data' => ['message' => $result['message']],
            'meta' => ['trace_id' => $request->attributes->get('trace_id')],
        ], 200);
    }

    public function changePassword(ChangePasswordRequest $request, ChangePasswordUseCase $useCase): JsonResponse
    {
        $userId = $request->attributes->get('auth_user_id');
        assert(is_string($userId) && $userId !== '');

        /** @var string $currentPassword */
        $currentPassword = $request->validated('current_password');
        /** @var string $newPassword */
        $newPassword = $request->validated('new_password');
        /** @var string $newPasswordConfirmation */
        $newPasswordConfirmation = $request->validated('new_password_confirmation');

        $dto = new ChangePasswordRequestDto(
            currentPassword: $currentPassword,
            newPassword: $newPassword,
            newPasswordConfirmation: $newPasswordConfirmation,
        );

        $result = $useCase->execute($dto, $userId);

        return response()->json([
            'data' => ['message' => $result['message']],
            'meta' => ['trace_id' => $request->attributes->get('trace_id')],
        ], 200);
    }

    public function updateProfile(UpdateProfileRequest $request, UpdateProfileUseCase $useCase): JsonResponse
    {
        $userId = $request->attributes->get('auth_user_id');
        assert(is_string($userId) && $userId !== '');

        /** @var string|null $name */
        $name = $request->validated('name');
        /** @var string|null $phone */
        $phone = $request->validated('phone');
        /** @var string|null $avatar */
        $avatar = $request->validated('avatar');

        $dto = new UpdateProfileRequestDto(
            name: $name,
            phone: $phone,
            avatar: $avatar,
        );

        $result = $useCase->execute($dto, $userId);

        $resource = new UserResource($result);

        return response()->json([
            'data' => $resource->resolve($request),
            'meta' => ['trace_id' => $request->attributes->get('trace_id')],
        ], 200);
    }

    public function verifyEmail(VerifyEmailRequest $request, VerifyEmailUseCase $useCase): JsonResponse
    {
        /** @var string $token */
        $token = $request->validated('token');

        $result = $useCase->execute($token);

        return response()->json([
            'data' => ['message' => $result['message']],
            'meta' => ['trace_id' => $request->attributes->get('trace_id')],
        ], 200);
    }

    public function resendVerification(Request $request, ResendVerificationUseCase $useCase): JsonResponse
    {
        $userId = $request->attributes->get('auth_user_id');
        assert(is_string($userId) && $userId !== '');

        $result = $useCase->execute($userId);

        return response()->json([
            'data' => ['message' => $result['message']],
            'meta' => ['trace_id' => $request->attributes->get('trace_id')],
        ], 200);
    }
}
