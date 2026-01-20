<?php

namespace App\Services;

use App\Enums\BookingState;
use App\Models\AuditLog;
use App\Models\Booking;
use App\Models\BookingItem;
use App\Models\Customer;
use App\Models\Ticket;
use Illuminate\Support\Facades\DB;
use InvalidArgumentException;

class BookingService
{
    public function __construct(
        private InventoryService $inventoryService
    ) {}

    /**
     * Create a draft booking
     */
    public function createDraft(array $customerData, array $items): Booking
    {
        return DB::transaction(function () use ($customerData, $items) {
            // Find or create customer
            $customer = Customer::firstOrCreate(
                ['email' => $customerData['email']],
                $customerData
            );

            // Create booking
            $booking = Booking::create([
                'customer_id' => $customer->id,
                'state' => BookingState::DRAFT,
                'state_changed_at' => now(),
                'currency' => 'AED',
                'booking_source' => $customerData['source'] ?? 'online',
                'created_by_user_id' => auth()->id(),
            ]);

            // Add items
            $subtotal = 0;
            foreach ($items as $itemData) {
                $ticket = Ticket::findOrFail($itemData['ticket_id']);
                
                $bookingItem = BookingItem::create([
                    'booking_id' => $booking->id,
                    'ticket_id' => $ticket->id,
                    'ticket_name' => $ticket->name,
                    'ticket_type' => $ticket->type,
                    'unit_price' => $ticket->price,
                    'quantity' => $itemData['quantity'],
                    'visit_date' => $itemData['visit_date'],
                    'visit_time' => $itemData['visit_time'] ?? null,
                ]);

                $subtotal += $bookingItem->subtotal;
            }

            // Update booking totals
            $tax = $subtotal * 0.05; // 5% VAT (adjust as needed)
            $booking->update([
                'subtotal' => $subtotal,
                'tax' => $tax,
                'total' => $subtotal + $tax,
                'visit_date' => $items[0]['visit_date'] ?? null,
            ]);

            AuditLog::log($booking, 'booking_created', 'Booking created in DRAFT state');

            return $booking->fresh(['items', 'customer']);
        });
    }

    /**
     * Create inventory hold and transition to HOLD state
     */
    public function createHold(Booking $booking, int $holdDurationMinutes = 15): Booking
    {
        $this->validateStateTransition($booking, BookingState::HOLD);

        return DB::transaction(function () use ($booking, $holdDurationMinutes) {
            // Check inventory availability
            foreach ($booking->items as $item) {
                if (!$this->inventoryService->checkAvailability(
                    $item->ticket_id,
                    $item->visit_date,
                    $item->quantity
                )) {
                    throw new InvalidArgumentException(
                        "Insufficient inventory for {$item->ticket_name} on {$item->visit_date}"
                    );
                }
            }

            // Create holds
            $expiresAt = now()->addMinutes($holdDurationMinutes);
            foreach ($booking->items as $item) {
                $this->inventoryService->createHold(
                    $booking,
                    $item->ticket_id,
                    $item->visit_date,
                    $item->quantity,
                    $expiresAt
                );
            }

            // Update booking state
            $booking->update([
                'state' => BookingState::HOLD,
                'state_changed_at' => now(),
                'hold_expires_at' => $expiresAt,
            ]);

            AuditLog::log(
                $booking,
                'state_changed',
                "Booking state changed to HOLD (expires at {$expiresAt})",
                BookingState::DRAFT->value,
                BookingState::HOLD->value
            );

            return $booking->fresh();
        });
    }

    /**
     * Transition to pending payment
     */
    public function startPayment(Booking $booking): Booking
    {
        $this->validateStateTransition($booking, BookingState::PENDING_PAYMENT);

        $booking->update([
            'state' => BookingState::PENDING_PAYMENT,
            'state_changed_at' => now(),
        ]);

        AuditLog::log(
            $booking,
            'state_changed',
            'Payment initiated',
            BookingState::HOLD->value,
            BookingState::PENDING_PAYMENT->value
        );

        return $booking->fresh();
    }

    /**
     * Confirm payment (called by webhook)
     * CRITICAL: This is the ONLY path to booking confirmation
     */
    public function confirmPayment(Booking $booking, string $transactionId): Booking
    {
        $this->validateStateTransition($booking, BookingState::PAID);

        return DB::transaction(function () use ($booking, $transactionId) {
            // Release holds (they're now confirmed)
            foreach ($booking->inventoryHolds as $hold) {
                $hold->release('payment_success');
            }

            // Update booking
            $booking->update([
                'state' => BookingState::PAID,
                'state_changed_at' => now(),
                'payment_reference' => $transactionId,
                'payment_provider' => 'cybersource',
            ]);

            AuditLog::log(
                $booking,
                'payment_confirmed',
                "Payment confirmed via webhook (Transaction: {$transactionId})",
                BookingState::PENDING_PAYMENT->value,
                BookingState::PAID->value
            );

            return $booking->fresh();
        });
    }

    /**
     * Issue tickets via Galaxy
     */
    public function issueTickets(Booking $booking, array $galaxyData): Booking
    {
        $this->validateStateTransition($booking, BookingState::TICKETS_ISSUED);

        $booking->update([
            'state' => BookingState::TICKETS_ISSUED,
            'state_changed_at' => now(),
            'galaxy_booking_id' => $galaxyData['booking_id'],
            'galaxy_tickets' => $galaxyData['tickets'],
            'tickets_issued_at' => now(),
        ]);

        AuditLog::log(
            $booking,
            'tickets_issued',
            "Tickets issued via Galaxy (Booking ID: {$galaxyData['booking_id']})",
            BookingState::PAID->value,
            BookingState::TICKETS_ISSUED->value
        );

        return $booking->fresh();
    }

    /**
     * Final confirmation
     */
    public function confirmBooking(Booking $booking): Booking
    {
        $this->validateStateTransition($booking, BookingState::CONFIRMED);

        $booking->update([
            'state' => BookingState::CONFIRMED,
            'state_changed_at' => now(),
        ]);

        AuditLog::log(
            $booking,
            'booking_confirmed',
            'Booking fully confirmed',
            BookingState::TICKETS_ISSUED->value,
            BookingState::CONFIRMED->value
        );

        return $booking->fresh();
    }

    /**
     * Mark payment as failed
     */
    public function markPaymentFailed(Booking $booking, string $reason): Booking
    {
        $this->validateStateTransition($booking, BookingState::PAYMENT_FAILED);

        $booking->update([
            'state' => BookingState::PAYMENT_FAILED,
            'state_changed_at' => now(),
            'notes' => $reason,
        ]);

        AuditLog::log(
            $booking,
            'payment_failed',
            "Payment failed: {$reason}",
            BookingState::PENDING_PAYMENT->value,
            BookingState::PAYMENT_FAILED->value
        );

        return $booking->fresh();
    }

    /**
     * Expire hold and release inventory
     */
    public function expireHold(Booking $booking): Booking
    {
        $this->validateStateTransition($booking, BookingState::EXPIRED);

        return DB::transaction(function () use ($booking) {
            // Release all holds
            foreach ($booking->inventoryHolds as $hold) {
                $hold->release('expired');
            }

            $booking->update([
                'state' => BookingState::EXPIRED,
                'state_changed_at' => now(),
            ]);

            AuditLog::log(
                $booking,
                'booking_expired',
                'Booking expired due to hold timeout',
                $booking->state->value,
                BookingState::EXPIRED->value
            );

            return $booking->fresh();
        });
    }

    /**
     * Cancel booking
     */
    public function cancelBooking(Booking $booking, string $reason = ''): Booking
    {
        $this->validateStateTransition($booking, BookingState::CANCELLED);

        return DB::transaction(function () use ($booking, $reason) {
            // Release any active holds
            foreach ($booking->inventoryHolds()->active()->get() as $hold) {
                $hold->release('cancelled');
            }

            $booking->update([
                'state' => BookingState::CANCELLED,
                'state_changed_at' => now(),
                'notes' => $reason,
            ]);

            AuditLog::log(
                $booking,
                'booking_cancelled',
                "Booking cancelled: {$reason}",
                $booking->state->value,
                BookingState::CANCELLED->value
            );

            return $booking->fresh();
        });
    }

    /**
     * Validate state transition
     */
    private function validateStateTransition(Booking $booking, BookingState $newState): void
    {
        if (!$booking->state->canTransitionTo($newState)) {
            throw new InvalidArgumentException(
                "Cannot transition from {$booking->state->value} to {$newState->value}"
            );
        }
    }
}
