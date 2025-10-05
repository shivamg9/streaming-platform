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
        Schema::create('stream_videos', function (Blueprint $table) {
            $table->id();
            $table->foreignId('stream_id')->constrained('streams')->onDelete('cascade');
            $table->foreignId('video_id')->constrained('videos')->onDelete('cascade');
            $table->integer('order')->default(0); // For playback order
            $table->timestamp('scheduled_time')->nullable(); // When to play this video
            $table->timestamp('played_at')->nullable(); // When it was actually played
            $table->integer('duration')->nullable(); // Duration to play (in seconds, null = full video)
            $table->boolean('is_looped')->default(false);
            $table->json('settings')->nullable(); // Additional playback settings
            $table->timestamps();

            $table->unique(['stream_id', 'video_id']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('stream_videos');
    }
};
