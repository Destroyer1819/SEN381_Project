<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('request_comments', function (Blueprint $table) {
            $table->id();
            $table->foreignId('request_id')->index()->constrained('service_requests')->cascadeOnUpdate()->restrictOnDelete();
            $table->foreignId('author_id')->index()->constrained('users')->cascadeOnUpdate()->restrictOnDelete();
            $table->text('comment');
            $table->boolean('is_resolution')->default(false);
            $table->timestamp('created_at')->useCurrent();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('request_comments');
    }
};
