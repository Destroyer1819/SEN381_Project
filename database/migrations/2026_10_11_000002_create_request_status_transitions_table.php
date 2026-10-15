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
        Schema::create('request_status_transitions', function (Blueprint $table) {
            $table->string('old_status');
            $table->string('new_status');

            $table->primary(['old_status', 'new_status']);
        });

        // The legal transitions are seeded here so they change only through a migration (D-015).
        DB::table('request_status_transitions')->insert([
            ['old_status' => 'open', 'new_status' => 'assigned'],
            ['old_status' => 'assigned', 'new_status' => 'in_progress'],
            ['old_status' => 'in_progress', 'new_status' => 'resolved'],
            ['old_status' => 'resolved', 'new_status' => 'closed'],
            ['old_status' => 'resolved', 'new_status' => 'in_progress'],
        ]);
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('request_status_transitions');
    }
};
