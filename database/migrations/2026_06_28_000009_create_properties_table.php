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
        Schema::create('properties', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->uuid('condominium_id');
            $table->uuid('tower_id');
            $table->uuid('property_type_id');
            $table->uuid('property_status_id');
            $table->smallInteger('floor');
            $table->string('unit_number', 20);
            $table->decimal('area_m2', 8, 2);
            $table->decimal('coefficient', 7, 6);
            $table->smallInteger('bedrooms')->nullable();
            $table->smallInteger('bathrooms')->nullable();
            $table->boolean('has_parking')->default(false);
            $table->string('parking_lot', 20)->nullable();
            $table->text('notes')->nullable();
            $table->timestamps();
            $table->softDeletes();

            $table->foreign('condominium_id')->references('id')->on('condominiums')->cascadeOnDelete();
            $table->foreign('tower_id')->references('id')->on('towers')->cascadeOnDelete();
            $table->foreign('property_type_id')->references('id')->on('property_types')->cascadeOnDelete();
            $table->foreign('property_status_id')->references('id')->on('property_statuses')->cascadeOnDelete();

            $table->index(['condominium_id', 'tower_id']);
            $table->index(['condominium_id', 'property_type_id']);
            $table->index(['condominium_id', 'property_status_id']);
            $table->index('coefficient');
            $table->index('deleted_at');
        });

        DB::statement('
            CREATE UNIQUE INDEX idx_properties_unit_unique
            ON properties (tower_id, floor, unit_number)
            WHERE deleted_at IS NULL
        ');

        DB::statement('ALTER TABLE properties ADD CONSTRAINT chk_properties_floor_non_negative CHECK (floor >= 0)');
        DB::statement('ALTER TABLE properties ADD CONSTRAINT chk_properties_area_positive CHECK (area_m2 > 0)');
        DB::statement('ALTER TABLE properties ADD CONSTRAINT chk_properties_coefficient_positive CHECK (coefficient > 0)');
    }

    public function down(): void
    {
        Schema::dropIfExists('properties');
    }
};
