<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Ticket extends Model
{
    use HasFactory;

    protected $fillable = [
        'ticket_id',
        'booking_id',
        'booking_item_id',
        'qr_code',
        'qr_image_path',
        'galaxy_ticket_id',
        'galaxy_response',
        'status',
        'used_at',
        'expires_at',
    ];

    protected $casts = [
        'galaxy_response' => 'array',
        'used_at' => 'datetime',
        'expires_at' => 'datetime',
    ];

    public function booking(): BelongsTo
    {
        return $this->belongsTo(Booking::class);
    }

    public function bookingItem(): BelongsTo
    {
        return $this->belongsTo(BookingItem::class);
    }
}
