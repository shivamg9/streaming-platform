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
        Schema::create('videos', function (Blueprint $table) {
            $table->id();
            $table->string('title');
            $table->text('description')->nullable();
            $table->string('filename');
            $table->string('original_filename');
            $table->string('mime_type');
            $table->bigInteger('file_size'); // in bytes
            $table->string('duration')->nullable(); // in seconds
            $table->integer('width')->nullable();
            $table->integer('height')->nullable();
            $table->string('thumbnail_path')->nullable();
            $table->string('video_path');
            $table->enum('status', ['processing', 'ready', 'failed'])->default('processing');
            $table->json('metadata')->nullable(); // For storing video metadata
            $table->foreignId('uploaded_by')->constrained('users')->onDelete('cascade');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('videos');
    }
};
