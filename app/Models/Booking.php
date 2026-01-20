<?php

namespace App\Models;

use App\Enums\BookingState;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\MorphMany;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Support\Str;

class Booking extends Model
{
    use HasFactory, SoftDeletes;

    protected $fillable = [
        'reference',        // Was booking_id
        'customer_id',      // Was customer_name/email/phone
        'visit_date',
        'visit_time',
        'notes',
        'subtotal',
        'tax',
        'total',            // Was total_amount
        'currency',
        'state',
        'state_changed_at',
        'hold_expires_at',
        'payment_method',
        'payment_provider',
        'payment_reference',
        'galaxy_booking_id',
        'galaxy_tickets',
        'tickets_issued_at',
        'created_by_user_id',
        'booking_source',
        'metadata',
    ];

    protected $casts = [
        'visit_date' => 'date',
        'subtotal' => 'decimal:2',
        'tax' => 'decimal:2',
        'total' => 'decimal:2',
        'state' => BookingState::class,
        'hold_expires_at' => 'datetime',
        'state_changed_at' => 'datetime',
        'tickets_issued_at' => 'datetime',
        'metadata' => 'array',
        'galaxy_tickets' => 'array',
    ];

    protected static function boot()
    {
        parent::boot();

        static::creating(function ($booking) {
            if (empty($booking->reference)) {
                $prefix = SystemSetting::get('booking_id_prefix', 'ZOO');
                $booking->reference = $prefix . '-' . date('Y') . '-' . strtoupper(Str::random(6));
            }
        });
    }

    public function customer(): BelongsTo
    {
        return $this->belongsTo(Customer::class);
    }

    public function items(): HasMany
    {
        return $this->hasMany(BookingItem::class);
    }

    public function payments(): HasMany
    {
        return $this->hasMany(Payment::class);
    }

    public function creator(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by_user_id');
    }

    public function activityLogs(): MorphMany
    {
        return $this->morphMany(ActivityLog::class, 'subject');
    }

    /**
     * Helper to get inventory holds related to this booking
     */
    public function inventoryHolds(): HasMany
    {
        return $this->hasMany(InventoryHold::class);
    }
}
