<?php

declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('communication_channels', function (Blueprint $table): void {
            $table->uuid('id')->primary();
            $table->foreignUuid('condominium_id')->constrained('condominiums')->cascadeOnDelete();
            $table->string('canal', 20);
            $table->string('provider', 50)->nullable();
            $table->jsonb('config')->nullable();
            $table->boolean('activo')->default(false);
            $table->timestamps();

            $table->index('condominium_id', 'idx_communication_channels_condominium');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('communication_channels');
    }
};
