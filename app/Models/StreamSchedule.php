<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class StreamSchedule extends Model
{
    protected $fillable = [
        'stream_id',
        'scheduled_start_time',
        'scheduled_end_time',
        'status',
        'is_recurring',
        'recurrence_pattern',
        'recurrence_interval',
        'next_occurrence',
        'settings',
    ];

    protected $casts = [
        'scheduled_start_time' => 'datetime',
        'scheduled_end_time' => 'datetime',
        'next_occurrence' => 'datetime',
        'is_recurring' => 'boolean',
        'recurrence_interval' => 'integer',
        'settings' => 'array',
    ];

    /**
     * Get the stream that is scheduled
     */
    public function stream(): BelongsTo
    {
        return $this->belongsTo(Stream::class);
    }

    /**
     * Check if schedule is pending
     */
    public function isPending(): bool
    {
        return $this->status === 'pending';
    }

    /**
     * Check if schedule is active
     */
    public function isActive(): bool
    {
        return $this->status === 'active';
    }

    /**
     * Check if schedule is completed
     */
    public function isCompleted(): bool
    {
        return $this->status === 'completed';
    }

    /**
     * Check if schedule is recurring
     */
    public function isRecurring(): bool
    {
        return $this->is_recurring;
    }
}
