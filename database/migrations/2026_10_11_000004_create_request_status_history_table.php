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
        Schema::create('request_status_history', function (Blueprint $table) {
            $table->id();
            $table->foreignId('request_id')->index()->constrained('service_requests')->cascadeOnUpdate()->restrictOnDelete();
            $table->string('old_status')->nullable();
            $table->string('new_status');
            $table->foreignId('changed_by')->index()->constrained('users')->cascadeOnUpdate()->restrictOnDelete();
            $table->timestamp('changed_at')->useCurrent();

            $table->index(['old_status', 'new_status']);
            $table->foreign(['old_status', 'new_status'])
                ->references(['old_status', 'new_status'])
                ->on('request_status_transitions')
                ->restrictOnUpdate()
                ->restrictOnDelete();
        });

        // CHECK constraints are PostgreSQL only; SQLite cannot add them to an existing table.
        if (DB::getDriverName() === 'pgsql') {
            DB::statement("ALTER TABLE request_status_history ADD CONSTRAINT new_status_options_check CHECK (new_status IN ('open', 'assigned', 'in_progress', 'resolved', 'closed'))");
            DB::statement("ALTER TABLE request_status_history ADD CONSTRAINT old_status_options_check CHECK (old_status IN ('open', 'assigned', 'in_progress', 'resolved', 'closed'))");
            DB::statement("ALTER TABLE request_status_history ADD CONSTRAINT old_status_first_entry_check CHECK (old_status IS NOT NULL OR new_status = 'open')");
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('request_status_history');
    }
};
