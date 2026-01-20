<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class StaffActivity extends Model
{
    use HasFactory;

    const UPDATED_AT = null; // Activity logs are append-only

    protected $fillable = [
        'user_id',
        'activity_type',
        'entity_type',
        'entity_id',
        'description',
        'metadata',
        'ip_address',
    ];

    protected $casts = [
        'metadata' => 'array',
        'created_at' => 'datetime',
    ];

    /**
     * User who performed the activity
     */
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    /**
     * Log a staff activity
     */
    public static function log(
        string $activityType,
        string $description,
        ?string $entityType = null,
        ?int $entityId = null,
        ?array $metadata = null
    ): self {
        return static::create([
            'user_id' => auth()->id(),
            'activity_type' => $activityType,
            'entity_type' => $entityType,
            'entity_id' => $entityId,
            'description' => $description,
            'metadata' => $metadata,
            'ip_address' => request()->ip(),
        ]);
    }
}
