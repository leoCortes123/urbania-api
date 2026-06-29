<?php

declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        DB::statement("
            INSERT INTO contacts (id, user_id, document_type, document_number, full_name, email, phone, organization_id, created_at, updated_at)
            SELECT gen_random_uuid(), u.id, 'CC',
                   LEFT(u.id::text, 15),
                   u.name, u.email, u.phone,
                   u.organization_id, NOW(), NOW()
            FROM users u
            WHERE u.deleted_at IS NULL
              AND NOT EXISTS (
                  SELECT 1 FROM contacts c WHERE c.user_id = u.id AND c.deleted_at IS NULL
              )
        ");
    }

    public function down(): void
    {
        // No se puede revertir de forma segura: los contacts creados pueden tener
        // relaciones (property_occupants). Se deja documentado como punto de no retorno.
    }
};
