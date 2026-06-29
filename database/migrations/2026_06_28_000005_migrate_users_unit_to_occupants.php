<?php

declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // Crear tabla de reconciliación para units que no hagan match
        Schema::create('reconciliation_users_unit', function ($table): void {
            $table->uuid('id')->primary();
            $table->uuid('user_id');
            $table->string('unit', 50);
            $table->uuid('organization_id')->nullable();
            $table->text('reason')->nullable();
            $table->timestamps();
        });

        // Insertar reportes de no-match antes de migrar los que sí coinciden
        DB::statement("
            INSERT INTO reconciliation_users_unit (id, user_id, unit, organization_id, reason, created_at, updated_at)
            SELECT gen_random_uuid(), u.id, u.unit, u.organization_id,
                   'No property found with matching unit_number within tenant',
                   NOW(), NOW()
            FROM users u
            WHERE u.deleted_at IS NULL
              AND u.unit IS NOT NULL
              AND u.unit != ''
              AND NOT EXISTS (
                  SELECT 1 FROM properties p
                  WHERE p.unit_number = u.unit AND p.deleted_at IS NULL
              )
        ");

        // Migrar matches encontrados a property_occupants
        DB::statement("
            INSERT INTO property_occupants (
                id, property_id, contact_id, occupant_type_id,
                is_primary, is_active, move_in_date, created_at, updated_at
            )
            SELECT
                gen_random_uuid(),
                p.id,
                c.id,
                (SELECT id FROM occupant_types WHERE code = 'residente' LIMIT 1),
                true,
                true,
                u.created_at::date,
                NOW(),
                NOW()
            FROM users u
            JOIN contacts c ON c.user_id = u.id AND c.deleted_at IS NULL
            JOIN properties p ON p.unit_number = u.unit AND p.deleted_at IS NULL
            WHERE u.unit IS NOT NULL
              AND u.unit != ''
              AND u.deleted_at IS NULL
              AND NOT EXISTS (
                  SELECT 1 FROM property_occupants po
                  WHERE po.contact_id = c.id
                    AND po.property_id = p.id
                    AND po.deleted_at IS NULL
              )
        ");
    }

    public function down(): void
    {
        // Se elimina la tabla de reconciliación; los occupants creados no se borran
        // porque pueden tener dependencias.
        Schema::dropIfExists('reconciliation_users_unit');
    }
};
