<?php

declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('permissions', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->string('resource', 50);       // ej: 'pagos', 'propiedades', 'directorio'
            $table->string('action', 50);          // ej: 'ver', 'crear', 'editar', 'aprobar'
            $table->string('name', 100);           // "Ver pagos"
            $table->text('description')->nullable();
            $table->boolean('is_system')->default(true);
            $table->timestamps();

            $table->unique(['resource', 'action']);
            $table->index('resource');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('permissions');
    }
};
