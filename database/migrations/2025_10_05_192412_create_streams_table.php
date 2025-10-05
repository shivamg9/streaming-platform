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
        Schema::create('streams', function (Blueprint $table) {
            $table->id();
            $table->string('title');
            $table->text('description')->nullable();
            $table->enum('type', ['live', 'prerecorded'])->default('live');
            $table->enum('status', ['scheduled', 'active', 'ended', 'cancelled'])->default('scheduled');
            $table->string('stream_key')->unique();
            $table->string('rtmp_url')->nullable();
            $table->string('hls_url')->nullable();
            $table->timestamp('scheduled_start_time')->nullable();
            $table->timestamp('actual_start_time')->nullable();
            $table->timestamp('end_time')->nullable();
            $table->integer('max_participants')->default(10);
            $table->boolean('is_public')->default(true);
            $table->json('settings')->nullable(); // For storing stream configuration
            $table->foreignId('host_id')->constrained('users')->onDelete('cascade');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('streams');
    }
};
