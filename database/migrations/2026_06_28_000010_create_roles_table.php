<?php

declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('roles', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->string('name', 100);
            $table->string('code', 50)->unique();  // 'admin', 'admin_conjunto', 'residente', etc.
            $table->text('description')->nullable();
            $table->uuid('organization_id')->nullable();  // null = rol de sistema
            $table->foreign('organization_id')->references('id')->on('organizations')->cascadeOnDelete();
            $table->string('level', 20)->default('condominium'); // organization, condominium, tower, unit
            $table->boolean('is_system')->default(false);
            $table->boolean('is_active')->default(true);
            $table->timestamps();
            $table->softDeletes();

            $table->index('organization_id');
            $table->index('code');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('roles');
    }
};
