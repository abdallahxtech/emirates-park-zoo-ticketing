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
        'booking_id',
        'customer_name',
        'customer_email',
        'customer_phone',
        'visit_date',
        'notes',
        'subtotal',
        'tax',
        'discount',
        'total_amount',
        'currency',
        'state',
        'expires_at', // Matching the migration column
        'cancellation_reason',
        'source',
        'created_by',
        'confirmed_at',
        'cancelled_at',
    ];

    protected $casts = [
        'visit_date' => 'date',
        'subtotal' => 'decimal:2',
        'tax' => 'decimal:2',
        'discount' => 'decimal:2',
        'total_amount' => 'decimal:2',
        'state' => BookingState::class,
        'expires_at' => 'datetime',
        'confirmed_at' => 'datetime',
        'cancelled_at' => 'datetime',
    ];

    protected static function boot()
    {
        parent::boot();

        static::creating(function ($booking) {
            if (empty($booking->booking_id)) {
                $prefix = SystemSetting::get('booking_id_prefix', 'ZOO');
                $booking->booking_id = $prefix . '-' . date('Y') . '-' . strtoupper(Str::random(6));
            }
        });
    }

    public function items(): HasMany
    {
        return $this->hasMany(BookingItem::class);
    }

    public function tickets(): HasMany
    {
        return $this->hasMany(Ticket::class);
    }

    public function payments(): HasMany
    {
        return $this->hasMany(Payment::class);
    }

    public function creator(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    public function activityLogs(): MorphMany
    {
        return $this->morphMany(ActivityLog::class, 'subject');
    }

    public function recalculateTotals(): void
    {
        $subtotal = $this->items->sum('subtotal');
        $taxRate = (float) SystemSetting::get('tax_rate', 0);
        $tax = $subtotal * ($taxRate / 100);
        $total = $subtotal + $tax - $this->discount;

        $this->update([
            'subtotal' => $subtotal,
            'tax' => $tax,
            'total_amount' => max(0, $total),
        ]);
    }

    public function markAsConfirmed(): void
    {
        if ($this->state !== BookingState::CONFIRMED) {
            $this->update([
                'state' => BookingState::CONFIRMED,
                'confirmed_at' => now(),
            ]);

            ActivityLog::logActivity($this, 'confirmed', null, null, null, 'Booking confirmed automatically');
        }
    }
}
