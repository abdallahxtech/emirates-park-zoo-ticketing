<?php

namespace App\Enums;

enum BookingState: string
{
    case DRAFT = 'draft';
    case PENDING_PAYMENT = 'pending_payment';
    case PAYMENT_PROCESSING = 'payment_processing';
    case PAYMENT_FAILED = 'payment_failed';
    case CONFIRMED = 'confirmed';
    case ISSUED = 'issued';
    case USED = 'used';
    case EXPIRED = 'expired';
    case CANCELLED = 'cancelled';

    /**
     * Get human-readable label
     */
    public function label(): string
    {
        return match ($this) {
            self::DRAFT => 'Draft',
            self::PENDING_PAYMENT => 'Awaiting Payment',
            self::PAYMENT_PROCESSING => 'Processing Payment',
            self::PAYMENT_FAILED => 'Payment Failed',
            self::CONFIRMED => 'Confirmed',
            self::ISSUED => 'Tickets Issued',
            self::USED => 'Used',
            self::EXPIRED => 'Expired',
            self::CANCELLED => 'Cancelled',
        };
    }

    /**
     * Get badge color for UI
     */
    public function color(): string
    {
        return match ($this) {
            self::DRAFT => 'gray',
            self::PENDING_PAYMENT => 'warning',
            self::PAYMENT_PROCESSING => 'info',
            self::PAYMENT_FAILED => 'danger',
            self::CONFIRMED => 'success',
            self::ISSUED => 'success',
            self::USED => 'primary',
            self::EXPIRED => 'danger',
            self::CANCELLED => 'danger',
        };
    }

    /**
     * Check if state can transition to another state
     */
    public function canTransitionTo(BookingState $newState): bool
    {
        return match ($this) {
            self::DRAFT => in_array($newState, [
                self::PENDING_PAYMENT,
                self::CANCELLED,
            ]),
            self::PENDING_PAYMENT => in_array($newState, [
                self::PAYMENT_PROCESSING,
                self::EXPIRED,
                self::CANCELLED,
            ]),
            self::PAYMENT_PROCESSING => in_array($newState, [
                self::CONFIRMED,
                self::PAYMENT_FAILED,
                self::PENDING_PAYMENT, // Failed payment returns to pending
                self::CANCELLED,
            ]),
            self::PAYMENT_FAILED => in_array($newState, [
                self::PENDING_PAYMENT, // Retry
                self::CANCELLED,
            ]),
            self::CONFIRMED => in_array($newState, [
                self::ISSUED,
                self::CANCELLED,
            ]),
            self::ISSUED => in_array($newState, [
                self::USED,
                self::EXPIRED,
                self::CANCELLED,
            ]),
            self::USED => false, // Terminal state
            self::EXPIRED => false, // Terminal state
            self::CANCELLED => false, // Terminal state
        };
    }
}
