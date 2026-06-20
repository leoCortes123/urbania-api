<?php

declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('login_attempts', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->uuid('user_id')->nullable();
            $table->foreign('user_id')->references('id')->on('users')->onDelete('set null');
            $table->string('email_attempted', 255);
            $table->string('ip_address');
            $table->text('user_agent')->nullable();
            $table->boolean('was_successful');
            $table->string('failure_reason', 50)->nullable();
            $table->boolean('mfa_used')->default(false);
            $table->timestamp('created_at')->useCurrent();

            $table->index(['ip_address', 'created_at']);
            $table->index(['email_attempted', 'created_at']);
            $table->index(['was_successful', 'created_at']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('login_attempts');
    }
};
