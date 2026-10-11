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
        Schema::create('service_requests', function (Blueprint $table) {
            $table->id();
            $table->bigInteger('reference_number')->unique();
            $table->foreignId('requestor_id')->index()->constrained('users')->cascadeOnUpdate()->restrictOnDelete();
            $table->foreignId('category_id')->index()->constrained('categories')->cascadeOnUpdate()->restrictOnDelete();
            $table->string('location');
            $table->string('area');
            $table->text('description');
            $table->string('status')->default('open');
            $table->foreignId('assigned_to')->nullable()->index()->constrained('users')->cascadeOnUpdate()->restrictOnDelete();
            $table->bigInteger('version')->default(0);
            $table->timestamp('created_at')->useCurrent();
            $table->timestamp('updated_at')->useCurrent();
            $table->timestamp('resolved_at')->nullable();
        });

        // CHECK constraints are PostgreSQL only; SQLite cannot add them to an existing table.
        if (DB::getDriverName() === 'pgsql') {
            DB::statement("ALTER TABLE service_requests ADD CONSTRAINT status_check CHECK (status IN ('open', 'assigned', 'in_progress', 'resolved', 'closed'))");
            DB::statement("ALTER TABLE service_requests ADD CONSTRAINT resolved_has_time CHECK (status NOT IN ('resolved', 'closed') OR resolved_at IS NOT NULL)");
            DB::statement('ALTER TABLE service_requests ADD CONSTRAINT resolved_after_updated_sanity_check CHECK (resolved_at IS NULL OR resolved_at >= created_at)');
            DB::statement('ALTER TABLE service_requests ADD CONSTRAINT updated_after_created_sanity_check CHECK (updated_at >= created_at)');
            DB::statement('ALTER TABLE service_requests ADD CONSTRAINT version_not_negative CHECK (version >= 0)');
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('service_requests');
    }
};
