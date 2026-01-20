<?php

namespace App\Processors;

use App\Models\Booking;
use App\Models\Payment;
use App\Models\PaymentWebhook;
use App\Services\BookingStateMachine;
use App\Enums\BookingState;
use Illuminate\Support\Facades\DB;

class PaymentWebhookProcessor
{
    public function __construct(
        protected BookingStateMachine $stateMachine
    ) {}

    public function process(PaymentWebhook $webhook): void
    {
        $payload = $webhook->payload;
        $bookingId = $payload['req_reference_number'] ?? null;
        $decision = $payload['decision'] ?? 'ERROR';
        $transactionId = $payload['transaction_id'] ?? null;

        if (!$bookingId) {
            $webhook->markAsFailed("Missing booking reference number");
            return;
        }

        $booking = Booking::where('booking_id', $bookingId)->first();

        if (!$booking) {
            $webhook->markAsFailed("Booking not found: $bookingId");
            return;
        }

        DB::transaction(function () use ($webhook, $booking, $payload, $decision, $transactionId) {
            // Record Payment Attempt
            $payment = Payment::create([
                'booking_id' => $booking->id,
                'payment_gateway' => 'cybersource',
                'transaction_id' => $transactionId,
                'amount' => $payload['auth_amount'] ?? $booking->total_amount,
                'currency' => $payload['req_currency'] ?? $booking->currency,
                'status' => $decision === 'ACCEPT' ? 'completed' : 'failed',
                'gateway_response' => $payload,
                'signature_verified' => true, // We verified it in controller
            ]);

            $webhook->payment()->associate($payment);

            // Update Booking State
            if ($decision === 'ACCEPT') {
                if ($booking->state !== BookingState::CONFIRMED) {
                    $this->stateMachine->transition($booking, BookingState::CONFIRMED, "Payment Accepted via Webhook");
                }
            } else {
                 $this->stateMachine->transition($booking, BookingState::PAYMENT_FAILED, "Payment Declined: " . ($payload['message'] ?? 'Unknown'));
            }

            $webhook->markAsProcessed();
        });
    }
}
