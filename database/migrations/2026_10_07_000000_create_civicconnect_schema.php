<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

/**
 * Creates the CivicConnect schema exactly as designed in the team's database
 * (database/sql/civicconnect_schema.sql, derived from SEN381_database_initial_version.sql).
 * The SQL file stays the single source of truth so the DB design is not
 * silently re-interpreted by an ORM migration.
 */
return new class extends Migration
{
    public function up(): void
    {
        DB::unprepared(file_get_contents(database_path('sql/civicconnect_schema.sql')));
    }

    public function down(): void
    {
        DB::unprepared('
            DROP TABLE IF EXISTS request_status_history, request_comments, request_assignments,
                service_request, request_status_transition, categories, users CASCADE;
        ');
    }
};
