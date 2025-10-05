<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Stream extends Model
{
    protected $fillable = [
        'title',
        'description',
        'type',
        'status',
        'stream_key',
        'rtmp_url',
        'hls_url',
        'scheduled_start_time',
        'actual_start_time',
        'end_time',
        'max_participants',
        'is_public',
        'settings',
        'host_id',
    ];

    protected $casts = [
        'scheduled_start_time' => 'datetime',
        'actual_start_time' => 'datetime',
        'end_time' => 'datetime',
        'is_public' => 'boolean',
        'settings' => 'array',
        'max_participants' => 'integer',
    ];

    /**
     * Get the host of the stream
     */
    public function host(): BelongsTo
    {
        return $this->belongsTo(User::class, 'host_id');
    }

    /**
     * Get all participants in the stream
     */
    public function participants(): HasMany
    {
        return $this->hasMany(StreamParticipant::class);
    }

    /**
     * Get active participants in the stream
     */
    public function activeParticipants(): HasMany
    {
        return $this->participants()->where('status', 'active');
    }

    /**
     * Get stream videos
     */
    public function streamVideos(): HasMany
    {
        return $this->hasMany(StreamVideo::class);
    }

    /**
     * Get stream schedules
     */
    public function schedules(): HasMany
    {
        return $this->hasMany(StreamSchedule::class);
    }
}
