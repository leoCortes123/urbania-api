<?php

declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('permission_audit_log', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->uuid('user_id')->nullable();
            $table->string('action', 50);       // 'check', 'grant', 'revoke'
            $table->string('resource', 50)->nullable();
            $table->string('result', 20);        // 'granted', 'denied'
            $table->jsonb('context')->nullable();
            $table->timestamp('created_at');

            $table->index(['user_id', 'created_at']);
            $table->index(['resource', 'created_at']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('permission_audit_log');
    }
};
