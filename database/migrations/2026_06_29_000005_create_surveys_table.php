<?php

declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('surveys', function (Blueprint $table): void {
            $table->uuid('id')->primary();
            $table->foreignUuid('condominium_id')->constrained('condominiums')->cascadeOnDelete();
            $table->string('pregunta', 500);
            $table->string('tipo', 20)->default('simple');
            $table->timestampTz('cierra_el')->nullable();
            $table->boolean('activa')->default(true);
            $table->timestamps();

            $table->index('condominium_id', 'idx_surveys_condominium');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('surveys');
    }
};
