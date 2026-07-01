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
        Schema::create('announcements', function (Blueprint $table): void {
            $table->uuid('id')->primary();
            $table->foreignUuid('condominium_id')->constrained('condominiums')->cascadeOnDelete();
            $table->foreignUuid('autor_user_id')->constrained('users')->cascadeOnDelete();
            $table->string('titulo', 255);
            $table->text('cuerpo');
            $table->string('segmento', 20);
            $table->uuid('target_id')->nullable();
            $table->string('estado', 20)->default('borrador');
            $table->timestampTz('programado_para')->nullable();
            $table->boolean('fijado')->default(false);
            $table->jsonb('canales')->nullable();
            $table->timestamps();
            $table->softDeletes();

            $table->index('condominium_id', 'idx_announcements_condominium');
            $table->index('estado', 'idx_announcements_estado');
        });

        DB::statement('CREATE INDEX idx_announcements_programado ON announcements(programado_para) WHERE programado_para IS NOT NULL');
    }

    public function down(): void
    {
        DB::statement('DROP INDEX IF EXISTS idx_announcements_programado');
        Schema::dropIfExists('announcements');
    }
};
