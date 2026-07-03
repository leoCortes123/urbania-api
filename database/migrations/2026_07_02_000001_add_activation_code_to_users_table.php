<?php

declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Agrega los campos de activación a la tabla users:
     * - activation_code: código de 6 dígitos para activación de cuenta
     * - activation_code_expires_at: vigencia del código (48h desde generación)
     *
     * Nota: el campo `status` ya existe como VARCHAR(20) —
     * el valor `pending_activation` (18 chars) cabe sin modificar la columna.
     */
    public function up(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->string('activation_code', 6)->nullable()->after('status');
            $table->timestamp('activation_code_expires_at')->nullable()->after('activation_code');
        });
    }

    public function down(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->dropColumn('activation_code_expires_at');
            $table->dropColumn('activation_code');
        });
    }
};
