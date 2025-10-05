<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class StreamVideo extends Model
{
    protected $fillable = [
        'stream_id',
        'video_id',
        'order',
        'scheduled_time',
        'played_at',
        'duration',
        'is_looped',
        'settings',
    ];

    protected $casts = [
        'scheduled_time' => 'datetime',
        'played_at' => 'datetime',
        'duration' => 'integer',
        'is_looped' => 'boolean',
        'settings' => 'array',
    ];

    /**
     * Get the stream that this video belongs to
     */
    public function stream(): BelongsTo
    {
        return $this->belongsTo(Stream::class);
    }

    /**
     * Get the video
     */
    public function video(): BelongsTo
    {
        return $this->belongsTo(Video::class);
    }

    /**
     * Check if video has been played
     */
    public function isPlayed(): bool
    {
        return !is_null($this->played_at);
    }

    /**
     * Mark video as played
     */
    public function markAsPlayed(): void
    {
        $this->update(['played_at' => now()]);
    }
}
