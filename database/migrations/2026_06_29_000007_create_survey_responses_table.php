<?php

declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('survey_responses', function (Blueprint $table): void {
            $table->uuid('id')->primary();
            $table->foreignUuid('survey_id')->constrained('surveys')->cascadeOnDelete();
            $table->foreignUuid('contact_id')->constrained('contacts')->cascadeOnDelete();
            $table->foreignUuid('option_id')->constrained('survey_options')->cascadeOnDelete();
            $table->timestamps();

            $table->unique(['survey_id', 'contact_id']);
            $table->index('survey_id', 'idx_survey_responses_survey');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('survey_responses');
    }
};
