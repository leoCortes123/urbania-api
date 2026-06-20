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
        Schema::create('password_history', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->uuid('user_id');
            $table->foreign('user_id')->references('id')->on('users')->onDelete('cascade');
            $table->string('password_hash', 255);
            $table->timestamp('created_at')->useCurrent();

            $table->index('user_id');
            $table->index(['user_id', 'created_at']);
        });

        DB::statement('CREATE OR REPLACE FUNCTION limit_password_history() RETURNS TRIGGER AS $$
BEGIN
    DELETE FROM password_history WHERE user_id = NEW.user_id
    AND id NOT IN (SELECT id FROM password_history WHERE user_id = NEW.user_id ORDER BY created_at DESC LIMIT 11);
    RETURN NEW;
END;
$$ LANGUAGE plpgsql;');

        DB::statement('CREATE TRIGGER trg_limit_password_history BEFORE INSERT ON password_history FOR EACH ROW EXECUTE FUNCTION limit_password_history();');
    }

    public function down(): void
    {
        DB::statement('DROP TRIGGER IF EXISTS trg_limit_password_history ON password_history;');
        DB::statement('DROP FUNCTION IF EXISTS limit_password_history();');
        Schema::dropIfExists('password_history');
    }
};
