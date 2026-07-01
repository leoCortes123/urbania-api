<?php

declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('approval_rules', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->string('resource', 50);
            $table->string('action', 50);
            $table->uuid('organization_id');
            $table->decimal('threshold', 15, 2)->nullable();
            $table->uuid('approver_role_id');
            $table->boolean('requires_second_approval')->default(false);
            $table->timestamps();

            $table->foreign('organization_id')->references('id')->on('organizations')->cascadeOnDelete();
            $table->foreign('approver_role_id')->references('id')->on('roles')->cascadeOnDelete();
            $table->index(['organization_id', 'resource', 'action']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('approval_rules');
    }
};
