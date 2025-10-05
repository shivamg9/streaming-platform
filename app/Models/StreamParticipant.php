<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class StreamParticipant extends Model
{
    protected $fillable = [
        'stream_id',
        'user_id',
        'role',
        'status',
        'joined_at',
        'left_at',
        'permissions',
    ];

    protected $casts = [
        'joined_at' => 'datetime',
        'left_at' => 'datetime',
        'permissions' => 'array',
    ];

    /**
     * Get the stream that the participant belongs to
     */
    public function stream(): BelongsTo
    {
        return $this->belongsTo(Stream::class);
    }

    /**
     * Get the user that is participating
     */
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    /**
     * Check if participant is active
     */
    public function isActive(): bool
    {
        return $this->status === 'active';
    }

    /**
     * Check if participant is a host
     */
    public function isHost(): bool
    {
        return $this->role === 'host';
    }

    /**
     * Check if participant is a guest
     */
    public function isGuest(): bool
    {
        return $this->role === 'guest';
    }
}
