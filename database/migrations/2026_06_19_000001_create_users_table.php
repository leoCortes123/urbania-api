<?php

declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('users', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->string('email', 255)->unique();
            $table->string('name', 255);
            $table->string('phone', 20)->nullable();
            $table->string('unit', 50)->nullable();
            $table->string('avatar_url', 500)->nullable();
            $table->string('password_hash', 255);
            $table->timestamp('email_verified_at')->nullable();
            $table->string('mfa_secret', 32)->nullable();
            $table->boolean('mfa_enabled')->default(false);
            $table->jsonb('mfa_backup_codes')->nullable();
            $table->smallInteger('failed_login_attempts')->default(0);
            $table->timestamp('locked_until')->nullable();
            $table->timestamp('last_login_at')->nullable();
            $table->string('last_login_ip')->nullable();
            $table->timestamp('password_changed_at')->nullable();
            $table->boolean('must_change_password')->default(false);
            $table->string('role', 20)->default('user');
            $table->string('status', 20)->default('active');
            $table->timestamps();
            $table->softDeletes();

            $table->index('role');
            $table->index('status');
            $table->index('locked_until');
            $table->index('deleted_at');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('users');
    }
};
