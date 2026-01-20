<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\MorphTo;

class AuditLog extends Model
{
    const UPDATED_AT = null; // Immutable logs don't have updated_at

    protected $fillable = [
        'auditable_type',
        'auditable_id',
        'event',
        'old_value',
        'new_value',
        'user_id',
        'ip_address',
        'user_agent',
        'metadata',
        'description',
    ];

    protected $casts = [
        'metadata' => 'array',
        'created_at' => 'datetime',
    ];

    /**
     * Get the auditable model
     */
    public function auditable(): MorphTo
    {
        return $this->morphTo();
    }

    /**
     * Get the user who triggered this event
     */
    public function user(): BelongsTo
    {
        return $this->belongsTo(\App\Models\User::class);
    }

    /**
     * Create a new audit log entry
     */
    public static function log(
        Model $auditable,
        string $event,
        ?string $description = null,
        ?string $oldValue = null,
        ?string $newValue = null,
        ?array $metadata = null
    ): self {
        return static::create([
            'auditable_type' => get_class($auditable),
            'auditable_id' => $auditable->id,
            'event' => $event,
            'old_value' => $oldValue,
            'new_value' => $newValue,
            'user_id' => auth()->id(),
            'ip_address' => request()->ip(),
            'user_agent' => request()->userAgent(),
            'metadata' => $metadata,
            'description' => $description,
        ]);
    }
}
