<?php

declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('property_documents', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->uuid('property_id');
            $table->uuid('property_document_type_id');
            $table->string('name', 255);
            $table->string('file_url', 500);
            $table->integer('file_size_bytes')->nullable();
            $table->string('mime_type', 100)->nullable();
            $table->text('notes')->nullable();
            $table->uuid('uploaded_by_user_id');
            $table->timestamp('created_at');
            $table->softDeletes();

            $table->foreign('property_id')->references('id')->on('properties')->cascadeOnDelete();
            $table->foreign('property_document_type_id')->references('id')->on('property_document_types')->cascadeOnDelete();
            $table->foreign('uploaded_by_user_id')->references('id')->on('users')->cascadeOnDelete();

            $table->index(['property_id', 'created_at']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('property_documents');
    }
};
