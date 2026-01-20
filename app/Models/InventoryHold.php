<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Builder;

class InventoryHold extends Model
{
    use HasFactory;

    protected $fillable = [
        'booking_id',
        'ticket_id',
        'visit_date',
        'quantity',
        'expires_at',
        'is_released',
        'released_at',
        'release_reason',
    ];

    protected $casts = [
        'visit_date' => 'date',
        'quantity' => 'integer',
        'expires_at' => 'datetime',
        'is_released' => 'boolean',
        'released_at' => 'datetime',
    ];

    /**
     * Get booking
     */
    public function booking(): BelongsTo
    {
        return $this->belongsTo(Booking::class);
    }

    /**
     * Get ticket
     */
    public function ticket(): BelongsTo
    {
        return $this->belongsTo(Ticket::class);
    }

    /**
     * Scope to get active holds (not released)
     */
    public function scopeActive(Builder $query): Builder
    {
        return $query->where('is_released', false);
    }

    /**
     * Scope to get expired holds
     */
    public function scopeExpired(Builder $query): Builder
    {
        return $query->where('is_released', false)
            ->where('expires_at', '<', now());
    }

    /**
     * Release this hold
     */
    public function release(string $reason = 'manual'): void
    {
        $this->update([
            'is_released' => true,
            'released_at' => now(),
            'release_reason' => $reason,
        ]);
    }

    /**
     * Check if hold is expired
     */
    public function isExpired(): bool
    {
        return !$this->is_released && $this->expires_at->isPast();
    }
}
