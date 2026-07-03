<?php

declare(strict_types=1);

namespace Urbania\Auth\Infrastructure\Http\Controllers;

use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Routing\Controller;
use Urbania\Auth\Application\DTOs\CreateInvitationRequestDto;
use Urbania\Auth\Application\DTOs\InvitationListItemDto;
use Urbania\Auth\Application\DTOs\RegisterByInvitationRequestDto;
use Urbania\Auth\Application\DTOs\UnitOccupantDto;
use Urbania\Auth\Application\UseCases\RegistroResidentes\CancelInvitationUseCase;
use Urbania\Auth\Application\UseCases\RegistroResidentes\CreateInvitationUseCase;
use Urbania\Auth\Application\UseCases\RegistroResidentes\ListInvitationsUseCase;
use Urbania\Auth\Application\UseCases\RegistroResidentes\ListUnitOccupantsUseCase;
use Urbania\Auth\Application\UseCases\RegistroResidentes\RegisterByInvitationUseCase;
use Urbania\Auth\Application\UseCases\RegistroResidentes\VerifyInvitationUseCase;
use Urbania\Auth\Infrastructure\Http\Requests\CreateInvitationRequest;
use Urbania\Auth\Infrastructure\Http\Requests\RegisterByInvitationRequest;

final class MyUnitController extends Controller
{
    // ── POST /my-unit/invitations ────────────────────────────
    public function createInvitation(CreateInvitationRequest $request, CreateInvitationUseCase $useCase): JsonResponse
    {
        /** @var object{id: string} $authUser */
        $authUser = $request->attributes->get('auth_user');

        /** @var string $inviteeEmail */
        $inviteeEmail = $request->validated('invitee_email');
        /** @var string $inviteeName */
        $inviteeName = $request->validated('invitee_name');
        /** @var string $occupantTypeId */
        $occupantTypeId = $request->validated('occupant_type_id');

        $dto = new CreateInvitationRequestDto(
            inviterUserId: $authUser->id,
            inviteeEmail: $inviteeEmail,
            inviteeName: $inviteeName,
            occupantTypeId: $occupantTypeId,
        );

        $result = $useCase->execute($dto);

        return response()->json([
            'data' => [
                'id' => $result->id,
                'token' => $result->token,
                'expires_at' => $result->expiresAt,
            ],
            'meta' => ['trace_id' => $request->attributes->get('trace_id')],
        ], 201);
    }

    // ── DELETE /my-unit/invitations/{id} ─────────────────────
    public function cancelInvitation(string $id, Request $request, CancelInvitationUseCase $useCase): JsonResponse
    {
        /** @var object{id: string} $authUser */
        $authUser = $request->attributes->get('auth_user');

        $useCase->execute($id, $authUser->id);

        return response()->json(null, 204);
    }

    // ── GET /invitations/{token} ─────────────────────────────
    public function verifyInvitation(string $token, Request $request, VerifyInvitationUseCase $useCase): JsonResponse
    {
        $result = $useCase->execute($token);

        return response()->json([
            'data' => [
                'id' => $result->id,
                'inviter_name' => $result->inviterName,
                'property_name' => $result->propertyName,
                'invitee_email' => $result->inviteeEmail,
                'invitee_name' => $result->inviteeName,
                'occupant_type' => $result->occupantType,
                'status' => $result->status,
                'expires_at' => $result->expiresAt,
            ],
            'meta' => ['trace_id' => $request->attributes->get('trace_id')],
        ], 200);
    }

    // ── POST /auth/register-by-invitation ────────────────────
    public function registerByInvitation(RegisterByInvitationRequest $request, RegisterByInvitationUseCase $useCase): JsonResponse
    {
        /** @var string $invitationToken */
        $invitationToken = $request->validated('invitation_token');
        /** @var string $password */
        $password = $request->validated('password');
        /** @var string $name */
        $name = $request->validated('name');
        /** @var string|null $phone */
        $phone = $request->validated('phone');

        $dto = new RegisterByInvitationRequestDto(
            invitationToken: $invitationToken,
            password: $password,
            name: $name,
            phone: $phone,
        );

        $result = $useCase->execute($dto);

        return response()->json([
            'data' => [
                'access_token' => $result->accessToken,
                'refresh_token' => $result->refreshToken,
                'token_type' => 'Bearer',
                'expires_in' => (int) $result->expiresIn,
            ],
            'meta' => ['trace_id' => $request->attributes->get('trace_id')],
        ], 201);
    }

    // ── GET /my-unit/occupants ───────────────────────────────
    public function listOccupants(Request $request, ListUnitOccupantsUseCase $useCase): JsonResponse
    {
        /** @var object{id: string} $authUser */
        $authUser = $request->attributes->get('auth_user');

        $result = $useCase->execute($authUser->id);

        return response()->json([
            'data' => array_map(fn (UnitOccupantDto $dto): array => [
                'occupant_id' => $dto->occupantId,
                'contact_id' => $dto->contactId,
                'user_id' => $dto->userId,
                'full_name' => $dto->fullName,
                'email' => $dto->email,
                'phone' => $dto->phone,
                'occupant_type' => $dto->occupantType,
                'is_legal_owner' => $dto->isLegalOwner,
                'is_portal_primary' => $dto->isPortalPrimary,
                'is_primary' => $dto->isPrimary,
                'user_status' => $dto->userStatus,
                'is_active' => $dto->isActive,
            ], $result),
            'meta' => ['trace_id' => $request->attributes->get('trace_id')],
        ], 200);
    }

    // ── GET /my-unit/invitations ─────────────────────────────
    public function listInvitations(Request $request, ListInvitationsUseCase $useCase): JsonResponse
    {
        /** @var object{id: string} $authUser */
        $authUser = $request->attributes->get('auth_user');

        /** @var string|null $status */
        $status = $request->query('status');

        $result = $useCase->execute($authUser->id, $status);

        return response()->json([
            'data' => array_map(fn (InvitationListItemDto $dto): array => [
                'id' => $dto->id,
                'invitee_email' => $dto->inviteeEmail,
                'invitee_name' => $dto->inviteeName,
                'occupant_type' => $dto->occupantType,
                'status' => $dto->status,
                'created_at' => $dto->createdAt,
                'expires_at' => $dto->expiresAt,
                'accepted_at' => $dto->acceptedAt,
            ], $result),
            'meta' => ['trace_id' => $request->attributes->get('trace_id')],
        ], 200);
    }
}
