<?php

declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('contacts', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->uuid('user_id')->nullable()->unique()->index();
            $table->foreign('user_id')->references('id')->on('users')->onDelete('set null');
            $table->string('document_type', 20);
            $table->string('document_number', 30);
            $table->string('full_name', 255);
            $table->string('email', 255)->nullable();
            $table->string('phone', 20)->nullable();
            $table->string('emergency_contact_name', 255)->nullable();
            $table->string('emergency_contact_phone', 20)->nullable();
            $table->text('notes')->nullable();
            $table->timestamps();
            $table->softDeletes();

            // Partial unique index: un documento activo por tipo+número
            $table->unique(['document_type', 'document_number'], 'idx_contacts_document_unique')
                ->whereNull('deleted_at');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('contacts');
    }
};
