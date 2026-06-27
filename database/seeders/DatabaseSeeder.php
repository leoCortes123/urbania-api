<?php

declare(strict_types=1);

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class DatabaseSeeder extends Seeder
{
    use WithoutModelEvents;

    /**
     * Seed the application's database.
     *
     * Crea los usuarios de prueba necesarios para desarrollo y testing.
     * Las credenciales coinciden con las definidas en WEB/.env.test
     * y WEB/.env.example para pruebas de integración Web → API.
     */
    public function run(): void
    {
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
        ]);
    }
}
