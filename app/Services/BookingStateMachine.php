<?php

namespace App\Services;

use App\Models\Booking;
use App\Models\ActivityLog;
use App\Models\User;
use App\Enums\BookingState;
use App\Jobs\IssueTicketsJob;
use App\Mail\BookingConfirmed;
use App\Mail\PaymentFailed;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Log;

class BookingStateMachine
{
    public function transition(Booking $booking, BookingState $toState, ?string $reason = null, ?User $actor = null): void
    {
        $fromState = $booking->state;

        if (!$fromState->canTransitionTo($toState)) {
             throw new \Exception("Invalid state transition from {$fromState->value} to {$toState->value}");
        }

        // Perform transition
        $booking->update([
            'state' => $toState,
            'cancellation_reason' => $reason, // Only relevant if cancelled
            'confirmed_at' => $toState === BookingState::CONFIRMED ? now() : $booking->confirmed_at,
            'cancelled_at' => $toState === BookingState::CANCELLED ? now() : $booking->cancelled_at,
        ]);

        // Log activity
        ActivityLog::logActivity(
            $booking,
            'state_changed',
            $actor,
            ['state' => $fromState->value],
            ['state' => $toState->value],
            "State changed to {$toState->label()}" . ($reason ? ": $reason" : '')
        );

        // Handle Side Effects
        $this->handleSideEffects($booking, $fromState, $toState);
    }

    protected function handleSideEffects(Booking $booking, BookingState $from, BookingState $to): void
    {
        Log::info("Handling side effects for booking {$booking->booking_id}: {$from->value} -> {$to->value}");

        match ($to) {
            BookingState::CONFIRMED => $this->onConfirmed($booking),
            BookingState::PAYMENT_FAILED => $this->onPaymentFailed($booking),
            BookingState::CANCELLED => $this->onCancelled($booking),
            default => null,
        };
    }

    protected function onConfirmed(Booking $booking): void
    {
        // 1. Issue Tickets (Async)
        // Dispatch job to generate tickets and send email
        IssueTicketsJob::dispatch($booking);

        // 2. Send WhatsApp Notification
        try {
            app(\App\Services\WhatsAppService::class)->sendConfirmation($booking);
        } catch (\Exception $e) {
            \Illuminate\Support\Facades\Log::error("WhatsApp notification failed: " . $e->getMessage());
        }

        // 3. Internal Notifications
        // Ops Alert (General)
        \Illuminate\Support\Facades\Mail::to(config('mail.from.address'))->send(new \App\Mail\OpsNewBooking($booking));

        // VIP Specific Handling
        foreach ($booking->items as $item) {
            // Heuristic: If price > 200 or defined as VIP in config
            // For now, checking if it has food preference is a good proxy for "Complex Experience"
            if (!empty($item->food_preference) || $item->product->base_price > 500) {
                
                // 1. Send VIP Confirmation to Customer (instead of/in addition to standard?)
                // Usually better to send ONE clear email. 
                // Let's assume high-ticket items get the VIP email, standard get standard.
                // For simplicity here, we send VIP email for the specific item.
                 \Illuminate\Support\Facades\Mail::to($booking->customer_email)->send(new \App\Mail\CustomerVipConfirmed($booking, $item));

                 // 2. Ops VIP Alert
                 \Illuminate\Support\Facades\Mail::to(config('mail.ops_email', 'ops@zoo.ae'))->send(new \App\Mail\OpsVipScheduled($booking, $item));

                 // 3. Kitchen Alert
                 if (!empty($item->food_preference)) {
                     \Illuminate\Support\Facades\Mail::to(config('mail.kitchen_email', 'kitchen@zoo.ae'))->send(new \App\Mail\KitchenPrepSheet($booking)); // Note: Updated Mailable to accept Item ideally, but Booking works
                 }
            }
        }
        
        // If NO VIP items, send standard confirmation
        // (Logic simplified: In real world, we check flags. Here assuming if not VIP, send standard)
        // \Illuminate\Support\Facades\Mail::to($booking->customer_email)->send(new \App\Mail\BookingConfirmed($booking));

        // 4. Sync to ERPNext (Future)
        // app(ErpNextService::class)->syncBooking($booking);
    }

    protected function onPaymentFailed(Booking $booking): void
    {
        // Send Failure Email
        // Mail::to($booking->customer_email)->send(new PaymentFailed($booking));
    }

    protected function onCancelled(Booking $booking): void
    {
        // Release capacity if held?
        // Refund if paid? logic here
    }
}
