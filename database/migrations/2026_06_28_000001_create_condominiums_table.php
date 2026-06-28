<?php

declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('condominiums', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->string('name', 255);
            $table->text('address')->nullable();
            $table->string('city', 100)->nullable();
            $table->string('department', 100)->nullable();
            $table->string('country', 100)->default('Colombia');
            $table->string('nit', 20)->nullable()->unique();
            $table->string('phone', 20)->nullable();
            $table->string('email', 255)->nullable();
            $table->string('legal_representative', 255)->nullable();
            $table->decimal('total_coefficient', 7, 6)->default('1.000000');
            $table->string('logo_url', 500)->nullable();
            $table->boolean('is_active')->default(true);
            $table->timestamps();
            $table->softDeletes();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('condominiums');
    }
};
