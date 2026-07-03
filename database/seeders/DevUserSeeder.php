<?php

declare(strict_types=1);

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Ramsey\Uuid\Uuid;

class DevUserSeeder extends Seeder
{
    use WithoutModelEvents;

    /**
     * Crea usuarios de prueba con todos los roles necesarios
     * para las Fases 1-3 del feature Registro de Residentes.
     *
     * ⚠️  SOLO se ejecuta en APP_ENV=local.
     *     En cualquier otro entorno, este seeder no hace nada.
     */
    public function run(): void
    {
        if (app()->environment('local') === false) {
            return;
        }

        $now = now()->toDateTimeString();
        $org = DB::table('organizations')->first();

        if ($org === null) {
            return; // sin organización base, no se pueden crear usuarios
        }

        $organizationId = $org->id;

        // ─────────────────────────────────────────────────
        // 1. Usuario pendiente de activación (Fase 2)
        //    Código conocido: 123456, expira en 48h
        // ─────────────────────────────────────────────────
        $this->createIfNotExists('users', 'email', 'pendiente@urbania.local', [
            'id' => Uuid::uuid7()->toString(),
            'email' => 'pendiente@urbania.local',
            'name' => 'Usuario Pendiente',
            'phone' => null,
            'avatar_url' => null,
            'password_hash' => Hash::make('Test2026!'),
            'email_verified_at' => null,
            'mfa_secret' => null,
            'mfa_enabled' => false,
            'mfa_backup_codes' => null,
            'failed_login_attempts' => 0,
            'locked_until' => null,
            'last_login_at' => null,
            'last_login_ip' => null,
            'password_changed_at' => null,
            'must_change_password' => true, // Forzar cambio en primer login
            'role' => 'user',
            'status' => 'pending_activation',
            'activation_code' => '123456',
            'activation_code_expires_at' => now()->addHours(48)->toDateTimeString(),
            'organization_id' => $organizationId,
            'created_at' => $now,
            'updated_at' => $now,
        ]);

        // ─────────────────────────────────────────────────
        // 2. Super-admin para pruebas de impersonation (Fase 5)
        // ─────────────────────────────────────────────────
        $this->createIfNotExists('users', 'email', 'superadmin@urbania.local', [
            'id' => Uuid::uuid7()->toString(),
            'email' => 'superadmin@urbania.local',
            'name' => 'Super Admin Local',
            'phone' => null,
            'avatar_url' => null,
            'password_hash' => Hash::make('Admin2026!'),
            'email_verified_at' => $now,
            'mfa_secret' => null,
            'mfa_enabled' => false,
            'mfa_backup_codes' => null,
            'failed_login_attempts' => 0,
            'locked_until' => null,
            'last_login_at' => null,
            'last_login_ip' => null,
            'password_changed_at' => null,
            'must_change_password' => false,
            'role' => 'admin',
            'status' => 'active',
            'activation_code' => null,
            'activation_code_expires_at' => null,
            'organization_id' => $organizationId,
            'created_at' => $now,
            'updated_at' => $now,
        ]);

        // ─────────────────────────────────────────────────
        // 3. Residente portal_primary de prueba (Fase 3)
        // ─────────────────────────────────────────────────
        $portalPrimaryId = $this->createIfNotExists('users', 'email', 'portal-primary@urbania.local', [
            'id' => Uuid::uuid7()->toString(),
            'email' => 'portal-primary@urbania.local',
            'name' => 'Portal Primary Test',
            'phone' => null,
            'avatar_url' => null,
            'password_hash' => Hash::make('Test2026!'),
            'email_verified_at' => $now,
            'mfa_secret' => null,
            'mfa_enabled' => false,
            'mfa_backup_codes' => null,
            'failed_login_attempts' => 0,
            'locked_until' => null,
            'last_login_at' => null,
            'last_login_ip' => null,
            'password_changed_at' => null,
            'must_change_password' => false,
            'role' => 'user',
            'status' => 'active',
            'activation_code' => null,
            'activation_code_expires_at' => null,
            'organization_id' => $organizationId,
            'created_at' => $now,
            'updated_at' => $now,
        ]);

        // ─────────────────────────────────────────────────
        // 4. Residente co-residente de prueba (Fase 3)
        // ─────────────────────────────────────────────────
        $this->createIfNotExists('users', 'email', 'co-residente@urbania.local', [
            'id' => Uuid::uuid7()->toString(),
            'email' => 'co-residente@urbania.local',
            'name' => 'Co-Residente Test',
            'phone' => null,
            'avatar_url' => null,
            'password_hash' => Hash::make('Test2026!'),
            'email_verified_at' => $now,
            'mfa_secret' => null,
            'mfa_enabled' => false,
            'mfa_backup_codes' => null,
            'failed_login_attempts' => 0,
            'locked_until' => null,
            'last_login_at' => null,
            'last_login_ip' => null,
            'password_changed_at' => null,
            'must_change_password' => false,
            'role' => 'user',
            'status' => 'active',
            'activation_code' => null,
            'activation_code_expires_at' => null,
            'organization_id' => $organizationId,
            'created_at' => $now,
            'updated_at' => $now,
        ]);

        // ── Crear contactos vinculados ──────────────────
        $adminContactId = $this->ensureContact('superadmin@urbania.local', $organizationId, 'CC', 'DEV00001', 'Super Admin Local');
        $portalContactId = $this->ensureContact('portal-primary@urbania.local', $organizationId, 'CC', 'DEV00003', 'Portal Primary Test');
        $coResidenteContactId = $this->ensureContact('co-residente@urbania.local', $organizationId, 'CC', 'DEV00004', 'Co-Residente Test');

        // ── Asignar roles RBAC ──────────────────────────
        $this->assignRole('superadmin@urbania.local', 'admin', 'organization', $organizationId);
        $this->assignRole('portal-primary@urbania.local', 'residente', 'organization', $organizationId);
        $this->assignRole('co-residente@urbania.local', 'residente', 'organization', $organizationId);

        // ── Crear occupants con los nuevos campos ───────
        $property = DB::table('properties')
            ->whereNull('deleted_at')
            ->first();

        if ($property !== null) {
            $propertyId = $property->id;

            $propietarioTypeId = $this->getOccupantTypeId('propietario');
            $residenteTypeId = $this->getOccupantTypeId('residente');

            if ($propietarioTypeId !== null) {
                // Portal primary como propietario + legal_owner
                $this->createOccupantIfNotExists($propertyId, $portalContactId, $propietarioTypeId, [
                    'is_primary' => true,
                    'is_legal_owner' => true,
                    'is_portal_primary' => true,
                    'is_active' => true,
                ]);

                // Super admin como propietario + legal_owner (para pruebas de admin viendo unidades)
                $this->createOccupantIfNotExists($propertyId, $adminContactId, $propietarioTypeId, [
                    'is_primary' => false,
                    'is_legal_owner' => true,
                    'is_portal_primary' => false,
                    'is_active' => true,
                ]);
            }

            if ($residenteTypeId !== null) {
                // Co-residente como residente (sin roles especiales)
                $this->createOccupantIfNotExists($propertyId, $coResidenteContactId, $residenteTypeId, [
                    'is_primary' => false,
                    'is_legal_owner' => false,
                    'is_portal_primary' => false,
                    'is_active' => true,
                ]);
            }
        }
    }

    // ═════════════════════════════════════════════════════
    // HELPERS
    // ═════════════════════════════════════════════════════

    /**
     * Inserta un registro solo si no existe por la columna dada.
     *
     * @return string El ID del registro (nuevo o existente)
     */
    private function createIfNotExists(string $table, string $uniqueColumn, string $uniqueValue, array $data): string
    {
        $existing = DB::table($table)->where($uniqueColumn, $uniqueValue)->first();

        if ($existing !== null) {
            return $existing->id;
        }

        DB::table($table)->insert($data);

        return $data['id'];
    }

    /**
     * Crea o recupera un contacto vinculado a un usuario.
     */
    private function ensureContact(string $email, string $organizationId, string $documentType, string $documentNumber, string $fullName): string
    {
        $user = DB::table('users')->where('email', $email)->first();
        if ($user === null) {
            throw new \RuntimeException("Usuario {$email} no encontrado para crear contacto.");
        }

        $existing = DB::table('contacts')
            ->where('user_id', $user->id)
            ->whereNull('deleted_at')
            ->first();

        if ($existing !== null) {
            return $existing->id;
        }

        $id = Uuid::uuid7()->toString();
        $now = now()->toDateTimeString();

        DB::table('contacts')->insert([
            'id' => $id,
            'user_id' => $user->id,
            'organization_id' => $organizationId,
            'document_type' => $documentType,
            'document_number' => $documentNumber,
            'full_name' => $fullName,
            'email' => $email,
            'phone' => null,
            'emergency_contact_name' => null,
            'emergency_contact_phone' => null,
            'notes' => null,
            'created_at' => $now,
            'updated_at' => $now,
        ]);

        return $id;
    }

    /**
     * Asigna un rol RBAC a un usuario por email.
     */
    private function assignRole(string $email, string $roleCode, string $scopeType, string $scopeId): void
    {
        $user = DB::table('users')->where('email', $email)->first();
        $role = DB::table('roles')->where('code', $roleCode)->first();

        if ($user === null || $role === null) {
            return;
        }

        $existing = DB::table('role_assignments')
            ->where('user_id', $user->id)
            ->where('role_id', $role->id)
            ->where('scope_type', $scopeType)
            ->where('scope_id', $scopeId)
            ->whereNull('deleted_at')
            ->first();

        if ($existing !== null) {
            return;
        }

        $now = now()->toDateTimeString();
        DB::table('role_assignments')->insert([
            'id' => Uuid::uuid7()->toString(),
            'user_id' => $user->id,
            'role_id' => $role->id,
            'scope_type' => $scopeType,
            'scope_id' => $scopeId,
            'starts_at' => $now,
            'ends_at' => null,
            'assigned_by_user_id' => null,
            'created_at' => $now,
            'updated_at' => $now,
        ]);
    }

    private function getOccupantTypeId(string $code): ?string
    {
        $type = DB::table('occupant_types')->where('code', $code)->first();

        return $type?->id;
    }

    /**
     * Crea un occupant si no existe, con los nuevos campos is_legal_owner e is_portal_primary.
     *
     * @param  array<string, mixed>  $extra  Campos adicionales (is_legal_owner, is_portal_primary, etc.)
     */
    private function createOccupantIfNotExists(string $propertyId, string $contactId, string $occupantTypeId, array $extra = []): void
    {
        $existing = DB::table('property_occupants')
            ->where('property_id', $propertyId)
            ->where('contact_id', $contactId)
            ->where('occupant_type_id', $occupantTypeId)
            ->whereNull('deleted_at')
            ->first();

        if ($existing !== null) {
            return;
        }

        $now = now()->toDateTimeString();
        DB::table('property_occupants')->insert(array_merge([
            'id' => Uuid::uuid7()->toString(),
            'property_id' => $propertyId,
            'contact_id' => $contactId,
            'occupant_type_id' => $occupantTypeId,
            'is_primary' => false,
            'is_legal_owner' => false,
            'is_portal_primary' => false,
            'move_in_date' => '2026-01-01',
            'move_out_date' => null,
            'is_active' => true,
            'created_at' => $now,
            'updated_at' => $now,
        ], $extra));
    }
}
