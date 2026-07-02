<?php

declare(strict_types=1);

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Ramsey\Uuid\Uuid;

// Directorio seeders

class DatabaseSeeder extends Seeder
{
    use WithoutModelEvents;

    /**
     * Seed the application's database.
     *
     * Orquesta todos los seeders del sistema en orden:
     * 1. Bootstrap: organización por defecto + usuarios de prueba + catálogos
     * 2. Catálogos: tipos de propiedad, estados, tipos de documento, tipos de ocupante
     * 3. Tenancy: asigna organización a registros huérfanos
     * 4. RBAC: permisos, roles, migración de roles legacy
     * 5. DemoData: datos de demostración para features 1-6
     *
     * ═══════════════════════════════════════════════════
     * ⚡ AL AGREGAR UN NUEVO FEATURE:
     *   1. Crear su seeder de datos demo en DemoDataSeeder
     *   2. No tocar este archivo a menos que el feature requiera bootstrap adicional
     * ═══════════════════════════════════════════════════
     *
     * Las credenciales coinciden con las definidas en WEB/.env.test
     * y WEB/.env.example para pruebas de integración Web → API.
     */
    public function run(): void
    {
        // ──────────────────────────────────────────────
        // Organización por defecto (tenant único inicial)
        // ──────────────────────────────────────────────
        $organizationId = $this->ensureDefaultOrganization();

        // ──────────────────────────────────────────────
        // Usuario administrador
        // ──────────────────────────────────────────────
        User::factory()->create([
            'name' => 'Admin Urbania',
            'email' => 'admin@urbania.com',
            'password_hash' => Hash::make('Admin2026!'),
            'role' => 'admin',
            'status' => 'active',
            'mfa_enabled' => false,
            'email_verified_at' => now(),
            'organization_id' => $organizationId,
        ]);

        // ──────────────────────────────────────────────
        // Usuario residente
        // ──────────────────────────────────────────────
        User::factory()->create([
            'name' => 'Residente Urbania',
            'email' => 'residente@urbania.com',
            'password_hash' => Hash::make('Resident2026!'),
            'role' => 'user',
            'status' => 'active',
            'mfa_enabled' => false,
            'email_verified_at' => now(),
            'organization_id' => $organizationId,
        ]);

        // ──────────────────────────────────────────────
        // Admin con MFA habilitado (para flujo completo)
        // ──────────────────────────────────────────────
        User::factory()->withMfa()->create([
            'name' => 'Admin MFA',
            'email' => 'admin-mfa@urbania.com',
            'password_hash' => Hash::make('Admin2026!'),
            'role' => 'admin',
            'status' => 'active',
            'email_verified_at' => now(),
            'organization_id' => $organizationId,
        ]);

        // ──────────────────────────────────────────────
        // Usuario bloqueado (para test de account locked)
        // ──────────────────────────────────────────────
        User::factory()->locked()->create([
            'name' => 'Bloqueado',
            'email' => 'bloqueado@urbania.com',
            'password_hash' => Hash::make('Bloqueado2026!'),
            'role' => 'user',
            'status' => 'active',
            'email_verified_at' => now(),
            'organization_id' => $organizationId,
        ]);

        $this->call([
            CondominiumSeeder::class,
            PropertyTypeSeeder::class,
            PropertyStatusSeeder::class,
            PropertyDocumentTypeSeeder::class,
            DirectorioSeeder::class,
            TenancyBootstrapSeeder::class,
            RbacPermissionSeeder::class,
            RbacRoleSeeder::class,
            RbacMigrationSeeder::class,
            DemoDataSeeder::class,
        ]);
    }

    /**
     * Crea o recupera la organización por defecto para el seed inicial.
     */
    private function ensureDefaultOrganization(): string
    {
        $existing = DB::table('organizations')->first();

        if ($existing !== null) {
            return $existing->id;
        }

        $id = Uuid::uuid7()->toString();
        $now = now();

        DB::table('organizations')->insert([
            'id' => $id,
            'name' => 'Urbania Default',
            'type' => 'edificio_unico',
            'nit' => '000000000-0',
            'email' => null,
            'country' => 'Colombia',
            'currency' => 'COP',
            'status' => 'activo',
            'logo_url' => null,
            'created_at' => $now,
            'updated_at' => $now,
        ]);

        return $id;
    }
}
