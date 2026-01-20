<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class PaymentWebhook extends Model
{
    use HasFactory;

    protected $fillable = [
        'payment_id',
        'webhook_id',
        'event_type',
        'idempotency_key',
        'processed',
        'processed_at',
        'payload',
        'signature',
        'signature_valid',
        'status',
        'error_message',
        'retry_count',
    ];

    protected $casts = [
        'processed' => 'boolean',
        'processed_at' => 'datetime',
        'payload' => 'array',
        'signature_valid' => 'boolean',
        'retry_count' => 'integer',
    ];

    /**
     * Get the payment this webhook belongs to
     */
    public function payment(): BelongsTo
    {
        return $this->belongsTo(Payment::class);
    }

    /**
     * Mark webhook as processed
     */
    public function markAsProcessed(): void
    {
        $this->update([
            'processed' => true,
            'processed_at' => now(),
            'status' => 'processed',
        ]);
    }

    /**
     * Mark webhook as failed
     */
    public function markAsFailed(string $errorMessage): void
    {
        $this->update([
            'status' => 'failed',
            'error_message' => $errorMessage,
            'retry_count' => $this->retry_count + 1,
        ]);
    }

    /**
     * Scope to get unprocessed webhooks
     */
    public function scopeUnprocessed($query)
    {
        return $query->where('processed', false);
    }

    /**
     * Scope to get failed webhooks
     */
    public function scopeFailed($query)
    {
        return $query->where('status', 'failed');
    }
}
