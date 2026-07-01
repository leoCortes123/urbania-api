<?php

declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('announcement_deliveries', function (Blueprint $table): void {
            $table->uuid('id')->primary();
            $table->foreignUuid('announcement_id')->constrained('announcements')->cascadeOnDelete();
            $table->foreignUuid('contact_id')->constrained('contacts')->cascadeOnDelete();
            $table->string('canal', 20);
            $table->string('estado', 20)->default('enviado');
            $table->string('external_id', 255)->nullable();
            $table->jsonb('metadata')->nullable();
            $table->timestamps();

            $table->index('announcement_id', 'idx_announcement_deliveries_announcement');
            $table->index('contact_id', 'idx_announcement_deliveries_contact');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('announcement_deliveries');
    }
};
