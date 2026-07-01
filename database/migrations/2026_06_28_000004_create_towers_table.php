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
        Schema::create('towers', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->uuid('condominium_id');
            $table->string('name', 100);
            $table->string('code', 20)->nullable();
            $table->smallInteger('floor_count')->default(1);
            $table->boolean('has_elevator')->default(false);
            $table->text('description')->nullable();
            $table->integer('sort_order')->default(0);
            $table->timestamps();
            $table->softDeletes();

            $table->foreign('condominium_id')->references('id')->on('condominiums')->cascadeOnDelete();
            $table->unique(['condominium_id', 'name']);
        });

        DB::statement('ALTER TABLE towers ADD CONSTRAINT chk_towers_floor_count_positive CHECK (floor_count > 0)');
    }

    public function down(): void
    {
        Schema::dropIfExists('towers');
    }
};
