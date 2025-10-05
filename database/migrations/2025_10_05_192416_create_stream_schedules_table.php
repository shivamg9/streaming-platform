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
        Schema::create('stream_schedules', function (Blueprint $table) {
            $table->id();
            $table->foreignId('stream_id')->constrained('streams')->onDelete('cascade');
            $table->timestamp('scheduled_start_time');
            $table->timestamp('scheduled_end_time')->nullable();
            $table->enum('status', ['pending', 'active', 'completed', 'cancelled'])->default('pending');
            $table->boolean('is_recurring')->default(false);
            $table->string('recurrence_pattern')->nullable(); // For recurring streams
            $table->integer('recurrence_interval')->nullable(); // In minutes
            $table->timestamp('next_occurrence')->nullable();
            $table->json('settings')->nullable(); // Additional scheduling settings
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('stream_schedules');
    }
};
