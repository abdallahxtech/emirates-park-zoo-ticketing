<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Payment extends Model
{
    use HasFactory;

    protected $fillable = [
        'booking_id',
        'payment_gateway',
        'transaction_id',
        'session_id',
        'amount',
        'currency',
        'status',
        'failure_reason',
        'gateway_request',
        'gateway_response',
        'refunded_amount',
        'refunded_at',
        'signature_verified',
        'webhook_signature',
    ];

    protected $casts = [
        'amount' => 'decimal:2',
        'refunded_amount' => 'decimal:2',
        'gateway_request' => 'array',
        'gateway_response' => 'array',
        'refunded_at' => 'datetime',
        'signature_verified' => 'boolean',
    ];

    public function booking(): BelongsTo
    {
        return $this->belongsTo(Booking::class);
    }

    public function webhooks(): HasMany
    {
        return $this->hasMany(PaymentWebhook::class);
    }

    public function isSuccessful(): bool
    {
        return $this->status === 'completed';
    }
}
