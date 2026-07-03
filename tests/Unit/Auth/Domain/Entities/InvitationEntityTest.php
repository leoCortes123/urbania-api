<?php

declare(strict_types=1);

use Urbania\Auth\Domain\Entities\InvitationEntity;

it('creates an invitation with default pending status', function (): void {
    $invitation = InvitationEntity::create(
        inviterUserId: '550e8400-e29b-41d4-a716-446655440001',
        propertyId: '550e8400-e29b-41d4-a716-446655440002',
        inviteeEmail: 'invitado@example.com',
        inviteeName: 'Juan Pérez',
        occupantTypeId: '550e8400-e29b-41d4-a716-446655440003',
    );

    expect($invitation->status())->toBe('pending');
    expect($invitation->token())->toHaveLength(128);
    expect($invitation->inviteeEmail())->toBe('invitado@example.com');
    expect($invitation->inviteeName())->toBe('Juan Pérez');
    expect($invitation->isPending())->toBeTrue();
    expect($invitation->isAccepted())->toBeFalse();
    expect($invitation->isRevoked())->toBeFalse();
    expect($invitation->isExpired())->toBeFalse();
    expect($invitation->acceptedAt())->toBeNull();
    expect($invitation->acceptedByUserId())->toBeNull();
    expect($invitation->revokedAt())->toBeNull();
});

it('creates invitation with expiry 72 hours in the future', function (): void {
    $before = (new DateTimeImmutable)->modify('+71 hours');
    $after = (new DateTimeImmutable)->modify('+73 hours');

    $invitation = InvitationEntity::create(
        inviterUserId: '550e8400-e29b-41d4-a716-446655440001',
        propertyId: '550e8400-e29b-41d4-a716-446655440002',
        inviteeEmail: 'invitado@example.com',
        inviteeName: 'Juan Pérez',
        occupantTypeId: '550e8400-e29b-41d4-a716-446655440003',
    );

    // expiresAt should be ~72h from now
    expect($invitation->expiresAt() > $before)->toBeTrue();
    expect($invitation->expiresAt() < $after)->toBeTrue();
    expect($invitation->isExpired())->toBeFalse();
});

it('can be marked as accepted', function (): void {
    $invitation = InvitationEntity::create(
        inviterUserId: '550e8400-e29b-41d4-a716-446655440001',
        propertyId: '550e8400-e29b-41d4-a716-446655440002',
        inviteeEmail: 'invitado@example.com',
        inviteeName: 'Juan Pérez',
        occupantTypeId: '550e8400-e29b-41d4-a716-446655440003',
    );

    $acceptedUserId = '550e8400-e29b-41d4-a716-446655440004';
    $invitation->markAsAccepted($acceptedUserId);

    expect($invitation->status())->toBe('accepted');
    expect($invitation->isAccepted())->toBeTrue();
    expect($invitation->isPending())->toBeFalse();
    expect($invitation->acceptedAt())->not->toBeNull();
    expect($invitation->acceptedByUserId())->toBe($acceptedUserId);
});

it('can be revoked', function (): void {
    $invitation = InvitationEntity::create(
        inviterUserId: '550e8400-e29b-41d4-a716-446655440001',
        propertyId: '550e8400-e29b-41d4-a716-446655440002',
        inviteeEmail: 'invitado@example.com',
        inviteeName: 'Juan Pérez',
        occupantTypeId: '550e8400-e29b-41d4-a716-446655440003',
    );

    $invitation->revoke();

    expect($invitation->status())->toBe('revoked');
    expect($invitation->isRevoked())->toBeTrue();
    expect($invitation->isPending())->toBeFalse();
    expect($invitation->revokedAt())->not->toBeNull();
});

it('reconstitutes an invitation from persisted data', function (): void {
    $now = new DateTimeImmutable;
    $expiresAt = $now->modify('+72 hours');

    $invitation = InvitationEntity::reconstitute(
        id: '550e8400-e29b-41d4-a716-446655440005',
        token: bin2hex(random_bytes(64)),
        inviterUserId: '550e8400-e29b-41d4-a716-446655440001',
        propertyId: '550e8400-e29b-41d4-a716-446655440002',
        inviteeEmail: 'invitado@example.com',
        inviteeName: 'Juan Pérez',
        occupantTypeId: '550e8400-e29b-41d4-a716-446655440003',
        status: 'pending',
        expiresAt: $expiresAt,
        createdAt: $now,
        updatedAt: $now,
    );

    expect($invitation->id()->toString())->toBe('550e8400-e29b-41d4-a716-446655440005');
    expect($invitation->status())->toBe('pending');
    expect($invitation->inviteeEmail())->toBe('invitado@example.com');
});
