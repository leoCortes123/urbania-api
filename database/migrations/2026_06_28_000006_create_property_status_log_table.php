<?php

declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('property_status_log', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->uuid('property_id');
            $table->uuid('from_status_id')->nullable();
            $table->uuid('to_status_id');
            $table->uuid('changed_by_user_id');
            $table->text('reason');
            $table->timestamp('created_at');

            $table->foreign('property_id')->references('id')->on('properties')->cascadeOnDelete();
            $table->foreign('from_status_id')->references('id')->on('property_statuses')->cascadeOnDelete();
            $table->foreign('to_status_id')->references('id')->on('property_statuses')->cascadeOnDelete();
            $table->foreign('changed_by_user_id')->references('id')->on('users')->cascadeOnDelete();

            $table->index(['property_id', 'created_at']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('property_status_log');
    }
};
