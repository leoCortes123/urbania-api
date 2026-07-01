<?php

declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('organizations', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->string('name', 255);
            $table->string('type', 20); // edificio_unico, administradora
            $table->string('nit', 20)->nullable()->unique();
            $table->string('email', 255)->nullable();
            $table->string('country', 100)->default('Colombia');
            $table->string('currency', 3)->default('COP');
            $table->string('status', 20)->default('trial'); // trial, activo, suspendido
            $table->string('logo_url', 500)->nullable();
            $table->timestamps();
            $table->softDeletes();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('organizations');
    }
};
