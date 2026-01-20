<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Support\Str;

class StaffInvitation extends Model
{
    use HasFactory;

    protected $fillable = [
        'email',
        'token',
        'role',
        'invited_by',
        'status',
        'accepted_at',
        'expires_at',
        'user_id',
    ];

    protected $casts = [
        'accepted_at' => 'datetime',
        'expires_at' => 'datetime',
    ];

    protected static function boot()
    {
        parent::boot();

        static::creating(function ($invitation) {
            if (empty($invitation->token)) {
                $invitation->token = Str::random(64);
            }

            if (empty($invitation->expires_at)) {
                $invitation->expires_at = now()->addDays(7);
            }
        });
    }

    /**
     * Get the user who sent the invitation
     */
    public function invitedBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'invited_by');
    }

    /**
     * Get the resulting user if accepted
     */
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class, 'user_id');
    }

    /**
     * Check if invitation is expired
     */
    public function isExpired(): bool
    {
        return $this->expires_at && $this->expires_at->isPast();
    }

    /**
     * Mark invitation as accepted
     */
    public function markAsAccepted(User $user): void
    {
        $this->update([
            'status' => 'accepted',
            'accepted_at' => now(),
            'user_id' => $user->id,
        ]);
    }

    /**
     * Regenerate token and extend expiry
     */
    public function regenerate(): void
    {
        $this->update([
            'token' => Str::random(64),
            'expires_at' => now()->addDays(7),
        ]);
    }

    /**
     * Scope to get pending invitations
     */
    public function scopePending($query)
    {
        return $query->where('status', 'pending')
                     ->where('expires_at', '>', now());
    }

    /**
     * Scope to get expired invitations
     */
    public function scopeExpired($query)
    {
        return $query->where('status', 'pending')
                     ->where('expires_at', '<=', now());
    }
}
