<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->string('role')->default('requestor');
        });

        // CHECK constraints are PostgreSQL only; SQLite cannot add them to an existing table.
        if (DB::getDriverName() === 'pgsql') {
            DB::statement("ALTER TABLE users ADD CONSTRAINT role_options CHECK (role IN ('requestor', 'staff', 'management'))");
            DB::statement('ALTER TABLE users ADD CONSTRAINT update_after_created_sanity_check CHECK (updated_at >= created_at)');
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        if (DB::getDriverName() === 'pgsql') {
            DB::statement('ALTER TABLE users DROP CONSTRAINT IF EXISTS role_options');
            DB::statement('ALTER TABLE users DROP CONSTRAINT IF EXISTS update_after_created_sanity_check');
        }

        Schema::table('users', function (Blueprint $table) {
            $table->dropColumn('role');
        });
    }
};
