<?php

declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // 1. Crear organización por defecto si aún no existe ninguna.
        DB::statement("INSERT INTO organizations (id, name, type, status, created_at, updated_at) SELECT gen_random_uuid(), 'Urbania Default', 'edificio_unico', 'activo', NOW(), NOW() WHERE NOT EXISTS (SELECT 1 FROM organizations LIMIT 1)");

        // 2. Asignar registros huérfanos a la organización por defecto.
        DB::statement('UPDATE users SET organization_id = (SELECT id FROM organizations LIMIT 1), updated_at = NOW() WHERE organization_id IS NULL');
        DB::statement('UPDATE condominiums SET organization_id = (SELECT id FROM organizations LIMIT 1), updated_at = NOW() WHERE organization_id IS NULL');
        DB::statement('UPDATE contacts SET organization_id = (SELECT id FROM organizations LIMIT 1), updated_at = NOW() WHERE organization_id IS NULL');

        // 3. Hacer organization_id NOT NULL en las tablas tenant.
        Schema::table('users', function (Blueprint $table) {
            $table->uuid('organization_id')->nullable(false)->change();
        });

        Schema::table('condominiums', function (Blueprint $table) {
            $table->uuid('organization_id')->nullable(false)->change();
        });

        Schema::table('contacts', function (Blueprint $table) {
            $table->uuid('organization_id')->nullable(false)->change();
        });
    }

    public function down(): void
    {
        Schema::table('contacts', function (Blueprint $table) {
            $table->uuid('organization_id')->nullable()->change();
        });

        Schema::table('condominiums', function (Blueprint $table) {
            $table->uuid('organization_id')->nullable()->change();
        });

        Schema::table('users', function (Blueprint $table) {
            $table->uuid('organization_id')->nullable()->change();
        });
    }
};
