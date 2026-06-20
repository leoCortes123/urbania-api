<?php

declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('security_events', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->uuid('user_id')->nullable();
            $table->foreign('user_id')->references('id')->on('users')->onDelete('set null');
            $table->string('event_type', 50);
            $table->string('severity', 20);
            $table->string('ip_address');
            $table->text('user_agent')->nullable();
            $table->jsonb('details')->nullable();
            $table->timestamp('created_at')->useCurrent();

            $table->index(['user_id', 'created_at']);
            $table->index(['event_type', 'created_at']);
            $table->index(['severity', 'created_at']);
        });

        DB::statement('CREATE INDEX idx_security_events_details ON security_events USING GIN (details);');
    }

    public function down(): void
    {
        Schema::dropIfExists('security_events');
    }
};
