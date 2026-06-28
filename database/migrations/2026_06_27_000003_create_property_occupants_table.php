<?php

declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('property_occupants', function (Blueprint $table) {
            $table->uuid('id')->primary();
            // Sin FK constraint formal porque properties aún no existe
            $table->uuid('property_id');
            $table->uuid('contact_id');
            $table->foreign('contact_id')->references('id')->on('contacts')->onDelete('restrict');
            $table->uuid('occupant_type_id');
            $table->foreign('occupant_type_id')->references('id')->on('occupant_types')->onDelete('restrict');
            $table->boolean('is_primary')->default(false);
            $table->date('move_in_date')->nullable();
            $table->date('move_out_date')->nullable();
            $table->boolean('is_active')->default(true);
            $table->timestamps();
            $table->softDeletes();

            // Índices
            $table->index('property_id');
            $table->index('contact_id');
            $table->index('occupant_type_id');

            // Partial unique: misma persona no puede tener mismo rol dos veces en misma unidad
            $table->unique(['property_id', 'contact_id', 'occupant_type_id'], 'idx_occupant_unique')
                ->whereNull('deleted_at');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('property_occupants');
    }
};
