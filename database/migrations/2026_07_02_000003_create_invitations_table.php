<?php

declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Crea la tabla de invitaciones a co-residentes.
     *
     * El portal_primary de una unidad envía invitaciones a co-residentes
     * (familiares, inquilinos, empleados). El invitado recibe un enlace con
     * token único (72h de vigencia) y completa su registro.
     */
    public function up(): void
    {
        Schema::create('invitations', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->string('token', 128)->unique();
            $table->foreignUuid('inviter_user_id')->constrained('users')->restrictOnDelete();
            $table->foreignUuid('property_id')->constrained('properties')->restrictOnDelete();
            $table->string('invitee_email', 255);
            $table->string('invitee_name', 255);
            $table->foreignUuid('occupant_type_id')->constrained('occupant_types')->restrictOnDelete();
            $table->string('status', 20)->default('pending');
            $table->timestamp('expires_at')->nullable();
            $table->timestamp('accepted_at')->nullable();
            $table->foreignUuid('accepted_by_user_id')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamp('revoked_at')->nullable();
            $table->timestamps();

            // Búsqueda de invitaciones pendientes por email
            $table->index(
                ['invitee_email', 'status'],
                'idx_invitations_email_status'
            );

            // Invitaciones activas de una unidad
            $table->index(
                ['property_id', 'status'],
                'idx_invitations_property_status'
            );
        });

        // Valor por defecto: NOW() + 72h (no expresable con la API fluida de Blueprint)
        DB::statement("ALTER TABLE invitations ALTER COLUMN expires_at SET DEFAULT (NOW() + INTERVAL '72 hours')");
    }

    public function down(): void
    {
        Schema::dropIfExists('invitations');
    }
};
