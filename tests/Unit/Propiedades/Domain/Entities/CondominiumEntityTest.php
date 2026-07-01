<?php

declare(strict_types=1);

namespace Tests\Unit\Propiedades\Domain\Entities;

use Urbania\Propiedades\Domain\Entities\CondominiumEntity;
use Urbania\Shared\Domain\ValueObjects\Uuid;

function createCondominium(array $overrides = []): CondominiumEntity
{
    return CondominiumEntity::create(
        $overrides['name'] ?? 'Condominio Primavera',
        $overrides['address'] ?? null,
        $overrides['city'] ?? null,
        $overrides['department'] ?? null,
        $overrides['country'] ?? null,
        $overrides['nit'] ?? null,
        $overrides['phone'] ?? null,
        $overrides['email'] ?? null,
        $overrides['legalRepresentative'] ?? null,
        $overrides['totalCoefficient'] ?? null,
        $overrides['logoUrl'] ?? null,
    );
}

it('creates a condominium with default values', function (): void {
    $condominium = createCondominium();

    expect($condominium->name())->toBe('Condominio Primavera')
        ->and($condominium->country())->toBe('Colombia')
        ->and($condominium->totalCoefficient())->toBe('1.000000')
        ->and($condominium->isActive())->toBeTrue()
        ->and($condominium->address())->toBeNull()
        ->and($condominium->city())->toBeNull()
        ->and($condominium->department())->toBeNull()
        ->and($condominium->nit())->toBeNull()
        ->and($condominium->phone())->toBeNull()
        ->and($condominium->email())->toBeNull()
        ->and($condominium->legalRepresentative())->toBeNull()
        ->and($condominium->logoUrl())->toBeNull()
        ->and($condominium->deletedAt())->toBeNull();
});

it('creates a condominium with all optional fields', function (): void {
    $condominium = createCondominium([
        'address' => 'Calle 123 # 45-67',
        'city' => 'Bogotá',
        'department' => 'Cundinamarca',
        'country' => 'Colombia',
        'nit' => '900123456',
        'phone' => '6011234567',
        'email' => 'contacto@primavera.com',
        'legalRepresentative' => 'Carlos López',
        'totalCoefficient' => '1.500000',
        'logoUrl' => 'https://example.com/logo.png',
    ]);

    expect($condominium->address())->toBe('Calle 123 # 45-67')
        ->and($condominium->city())->toBe('Bogotá')
        ->and($condominium->department())->toBe('Cundinamarca')
        ->and($condominium->country())->toBe('Colombia')
        ->and($condominium->nit())->toBe('900123456')
        ->and($condominium->phone())->toBe('6011234567')
        ->and($condominium->email())->toBe('contacto@primavera.com')
        ->and($condominium->legalRepresentative())->toBe('Carlos López')
        ->and($condominium->totalCoefficient())->toBe('1.500000')
        ->and($condominium->logoUrl())->toBe('https://example.com/logo.png');
});

it('update modifies fields and updates updatedAt', function (): void {
    $condominium = createCondominium();
    $previousUpdatedAt = $condominium->updatedAt();

    usleep(1000);

    $condominium->update(
        name: 'Condominio Verano',
        address: 'Avenida 1 # 2-3',
        city: 'Medellín',
        department: 'Antioquia',
        country: 'Colombia',
        nit: '900987654',
        phone: '6047654321',
        email: 'info@verano.com',
        legalRepresentative: 'Ana Martínez',
        totalCoefficient: '2.000000',
        logoUrl: 'https://example.com/verano.png',
    );

    expect($condominium->name())->toBe('Condominio Verano')
        ->and($condominium->address())->toBe('Avenida 1 # 2-3')
        ->and($condominium->city())->toBe('Medellín')
        ->and($condominium->department())->toBe('Antioquia')
        ->and($condominium->country())->toBe('Colombia')
        ->and($condominium->nit())->toBe('900987654')
        ->and($condominium->phone())->toBe('6047654321')
        ->and($condominium->email())->toBe('info@verano.com')
        ->and($condominium->legalRepresentative())->toBe('Ana Martínez')
        ->and($condominium->totalCoefficient())->toBe('2.000000')
        ->and($condominium->logoUrl())->toBe('https://example.com/verano.png')
        ->and($condominium->updatedAt())->toBeGreaterThan($previousUpdatedAt);
});

it('returns true for isActive by default', function (): void {
    $condominium = createCondominium();

    expect($condominium->isActive())->toBeTrue();
});

it('exposes all getters', function (): void {
    $condominium = createCondominium([
        'address' => 'Calle 123',
        'city' => 'Bogotá',
    ]);

    expect($condominium->id())->toBeInstanceOf(Uuid::class)
        ->and($condominium->name())->toBeString()
        ->and($condominium->address())->toBeString()
        ->and($condominium->city())->toBeString()
        ->and($condominium->department())->toBeNull()
        ->and($condominium->country())->toBeString()
        ->and($condominium->nit())->toBeNull()
        ->and($condominium->phone())->toBeNull()
        ->and($condominium->email())->toBeNull()
        ->and($condominium->legalRepresentative())->toBeNull()
        ->and($condominium->totalCoefficient())->toBeString()
        ->and($condominium->logoUrl())->toBeNull()
        ->and($condominium->isActive())->toBeBool()
        ->and($condominium->createdAt())->toBeInstanceOf(\DateTimeImmutable::class)
        ->and($condominium->updatedAt())->toBeInstanceOf(\DateTimeImmutable::class)
        ->and($condominium->deletedAt())->toBeNull();
});
