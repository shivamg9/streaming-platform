<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Video extends Model
{
    protected $fillable = [
        'title',
        'description',
        'filename',
        'original_filename',
        'mime_type',
        'file_size',
        'duration',
        'width',
        'height',
        'thumbnail_path',
        'video_path',
        'status',
        'metadata',
        'uploaded_by',
    ];

    protected $casts = [
        'file_size' => 'integer',
        'duration' => 'integer',
        'width' => 'integer',
        'height' => 'integer',
        'metadata' => 'array',
    ];

    /**
     * Get the user who uploaded the video
     */
    public function uploader(): BelongsTo
    {
        return $this->belongsTo(User::class, 'uploaded_by');
    }

    /**
     * Get all stream videos associated with this video
     */
    public function streamVideos(): HasMany
    {
        return $this->hasMany(StreamVideo::class);
    }

    /**
     * Check if video is ready for playback
     */
    public function isReady(): bool
    {
        return $this->status === 'ready';
    }

    /**
     * Check if video is still processing
     */
    public function isProcessing(): bool
    {
        return $this->status === 'processing';
    }

    /**
     * Get video URL
     */
    public function getUrl(): string
    {
        return asset('storage/' . $this->video_path);
    }

    /**
     * Get thumbnail URL
     */
    public function getThumbnailUrl(): ?string
    {
        return $this->thumbnail_path ? asset('storage/' . $this->thumbnail_path) : null;
    }
}
