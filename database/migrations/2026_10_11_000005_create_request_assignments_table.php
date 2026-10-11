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
        Schema::create('request_assignments', function (Blueprint $table) {
            $table->id();
            $table->foreignId('request_id')->index()->constrained('service_requests')->cascadeOnUpdate()->restrictOnDelete();
            $table->foreignId('assigned_to')->index()->constrained('users')->cascadeOnUpdate()->restrictOnDelete();
            $table->foreignId('offered_by')->index()->constrained('users')->cascadeOnUpdate()->restrictOnDelete();
            $table->string('state');
            $table->timestamp('created_at')->useCurrent();
            $table->timestamp('responded_at')->nullable();
        });

        // CHECK constraints are PostgreSQL only; SQLite cannot add them to an existing table.
        if (DB::getDriverName() === 'pgsql') {
            DB::statement("ALTER TABLE request_assignments ADD CONSTRAINT state_options CHECK (state IN ('offered', 'accepted', 'declined', 'self_assigned'))");
            DB::statement("ALTER TABLE request_assignments ADD CONSTRAINT self_assign_same_user CHECK (state <> 'self_assigned' OR assigned_to = offered_by)");
            DB::statement('ALTER TABLE request_assignments ADD CONSTRAINT responded_after_created CHECK (responded_at IS NULL OR responded_at >= created_at)');
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('request_assignments');
    }
};
