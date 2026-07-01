<?php

declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('survey_options', function (Blueprint $table): void {
            $table->uuid('id')->primary();
            $table->foreignUuid('survey_id')->constrained('surveys')->cascadeOnDelete();
            $table->string('texto', 255);
            $table->integer('orden')->default(0);
            $table->timestamps();

            $table->index('survey_id', 'idx_survey_options_survey');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('survey_options');
    }
};
