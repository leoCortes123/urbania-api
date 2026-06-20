<?php

declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('refresh_tokens', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->uuid('user_id');
            $table->foreign('user_id')->references('id')->on('users')->onDelete('cascade');
            $table->uuid('session_id');
            $table->char('token_hash', 64)->unique();
            $table->uuid('token_family');
            $table->char('previous_token_hash', 64)->nullable();
            $table->string('device_fingerprint', 64);
            $table->string('device_name', 255)->nullable();
            $table->string('ip_address')->nullable();
            $table->text('user_agent')->nullable();
            $table->timestamp('expires_at');
            $table->timestamp('revoked_at')->nullable();
            $table->string('revocation_reason', 50)->nullable();
            $table->timestamp('last_used_at')->nullable();
            $table->timestamp('created_at')->useCurrent();

            $table->index('session_id');
            $table->index('token_family');
            $table->index('expires_at');
            $table->index('device_fingerprint');
            $table->index(['user_id', 'revoked_at']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('refresh_tokens');
    }
};
