<?php

declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Agrega dos columnas nuevas a property_occupants:
     * - is_legal_owner: responsable legal de la unidad (Ley 675). Múltiples por unidad permitidos.
     * - is_portal_primary: quién gestiona el portal de la unidad. Solo 1 por unidad (total).
     *
     * La columna existente `is_primary` NO se modifica — conserva su semántica
     * de "contacto principal por rol" del feature Directorio.
     *
     * Sin backfill: todas las filas existentes quedan con FALSE en ambos campos.
     */
    public function up(): void
    {
        Schema::table('property_occupants', function (Blueprint $table) {
            $table->boolean('is_legal_owner')->default(false)->after('is_primary');
            $table->boolean('is_portal_primary')->default(false)->after('is_legal_owner');
        });

        // Partial unique index: solo un portal_primary activo por unidad
        DB::statement('
            CREATE UNIQUE INDEX idx_property_occupants_portal_primary
            ON property_occupants (property_id)
            WHERE is_portal_primary = TRUE AND deleted_at IS NULL
        ');
    }

    public function down(): void
    {
        DB::statement('DROP INDEX IF EXISTS idx_property_occupants_portal_primary');

        Schema::table('property_occupants', function (Blueprint $table) {
            $table->dropColumn('is_portal_primary');
            $table->dropColumn('is_legal_owner');
        });
    }
};
