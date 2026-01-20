<?php

namespace App\Services;

use App\Models\AuditLog;
use App\Models\Booking;
use App\Models\Payment;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class PaymentService
{
    private string $merchantId;
    private string $apiKey;
    private string $sharedSecret;
    private string $apiEndpoint;
    private string $webhookSecret;

    public function __construct()
    {
        $this->merchantId = config('cybersource.merchant_id');
        $this->apiKey = config('cybersource.api_key');
        $this->sharedSecret = config('cybersource.shared_secret');
        $this->apiEndpoint = config('cybersource.api_endpoint');
        $this->webhookSecret = config('cybersource.webhook_secret');
    }

    /**
     * Initiate payment with CyberSource Secure Acceptance
     */
    public function initiatePayment(Booking $booking): array
    {
        $transactionId = 'TXN-' . $booking->reference . '-' . time();
        
        // Create payment record
        $payment = Payment::create([
            'booking_id' => $booking->id,
            'transaction_id' => $transactionId,
            'idempotency_key' => $transactionId,
            'amount' => $booking->total,
            'currency' => $booking->currency,
            'status' => 'pending',
        ]);

        // Build secure fields data for WordPress frontend
        $signedFields = [
            'access_key' => $this->apiKey,
            'profile_id' => config('cybersource.profile_id'),
            'transaction_uuid' => $transactionId,
            'signed_field_names' => 'access_key,profile_id,transaction_uuid,signed_field_names,unsigned_field_names,signed_date_time,locale,transaction_type,reference_number,amount,currency,merchant_defined_data1',
            'unsigned_field_names' => '',
            'signed_date_time' => gmdate("Y-m-d\TH:i:s\Z"),
            'locale' => 'en',
            'transaction_type' => 'sale',
            'reference_number' => $booking->reference,
            'amount' => (string) $booking->total,
            'currency' => $booking->currency,
            'merchant_defined_data1' => $booking->id, // For webhook lookup
        ];

        // Generate signature
        $signedFields['signature'] = $this->generateSignature($signedFields);

        AuditLog::log(
            $payment,
            'payment_initiated',
            "Payment session created for booking {$booking->reference}",
            null,
            null,
            ['transaction_id' => $transactionId]
        );

        return [
            'payment_id' => $payment->id,
            'transaction_id' => $transactionId,
            'secure_fields' => $signedFields,
            'payment_url' => config('cybersource.payment_url'),
        ];
    }

    /**
     * Verify webhook signature
     */
    public function verifyWebhookSignature(array $payload, string $signature): bool
    {
        // Build data string in CyberSource order
        $dataToSign = [];
        foreach (explode(',', $payload['signed_field_names'] ?? '') as $field) {
            if (isset($payload[$field])) {
                $dataToSign[] = $field . '=' . $payload[$field];
            }
        }
        
        $dataString = implode(',', $dataToSign);
        $expectedSignature = base64_encode(hash_hmac('sha256', $dataString, $this->webhookSecret, true));

        return hash_equals($expectedSignature, $signature);
    }

    /**
     * Handle webhook notification (IDEMPOTENT)
     * CRITICAL: This is the ONLY trusted source for payment confirmation
     */
    public function handleWebhookNotification(array $payload): array
    {
        return DB::transaction(function () use ($payload) {
            $transactionId = $payload['transaction_id'] ?? $payload['transaction_uuid'] ?? null;
            $bookingId = $payload['merchant_defined_data1'] ?? $payload['req_merchant_defined_data1'] ?? null;
            $decision = $payload['decision'] ?? null;
            $signature = $payload['signature'] ?? null;

            Log::info('CyberSource webhook received', [
                'transaction_id' => $transactionId,
                'booking_id' => $bookingId,
                'decision' => $decision,
            ]);

            // Verify signature
            if (!$this->verifyWebhookSignature($payload, $signature)) {
                Log::error('Invalid webhook signature', ['payload' => $payload]);
                throw new \Exception('Invalid webhook signature');
            }

            // Find payment record (idempotency check)
            $payment = Payment::where('transaction_id', $transactionId)->first();
            
            if (!$payment) {
                Log::error('Payment not found for webhook', ['transaction_id' => $transactionId]);
                throw new \Exception('Payment record not found');
            }

            // Idempotency: Skip if already processed
            if ($payment->webhook_received_at) {
                Log::info('Webhook already processed (idempotent)', [
                    'transaction_id' => $transactionId,
                    'processed_at' => $payment->webhook_received_at,
                ]);
                
                return [
                    'status' => 'already_processed',
                    'booking_id' => $payment->booking_id,
                ];
            }

            // Update payment with webhook data
            $payment->update([
                'webhook_payload' => $payload,
                'webhook_signature' => $signature,
                'webhook_received_at' => now(),
                'webhook_attempts' => $payment->webhook_attempts + 1,
                'reference_number' => $payload['transaction_id'] ?? null,
                'card_last4' => $payload['req_card_number'] ? substr($payload['req_card_number'], -4) : null,
                'card_type' => $payload['req_card_type'] ?? null,
            ]);

            $booking = $payment->booking;

            // Process based on decision
            if ($decision === 'ACCEPT') {
                return $this->processAccept($payment, $booking, $payload);
            } elseif ($decision === 'DECLINE' || $decision === 'ERROR') {
                return $this->processDecline($payment, $booking, $payload);
            } else {
                Log::warning('Unknown payment decision', ['decision' => $decision]);
                return ['status' => 'unknown', 'decision' => $decision];
            }
        });
    }

    /**
     * Process ACCEPT decision
     */
    private function processAccept(Payment $payment, Booking $booking, array $payload): array
    {
        $payment->update([
            'status' => 'accepted',
        ]);

        AuditLog::log(
            $payment,
            'payment_accepted',
            "Payment ACCEPTED via webhook",
            'pending',
            'accepted',
            ['decision' => 'ACCEPT', 'transaction_id' => $payment->transaction_id]
        );

        // Confirm booking via BookingService
        $bookingService = app(BookingService::class);
        $bookingService->confirmPayment($booking, $payment->transaction_id);

        Log::info('Payment accepted and booking confirmed', [
            'booking_id' => $booking->id,
            'reference' => $booking->reference,
            'transaction_id' => $payment->transaction_id,
        ]);

        return [
            'status' => 'accepted',
            'booking_id' => $booking->id,
            'booking_reference' => $booking->reference,
        ];
    }

    /**
     * Process DECLINE decision
     */
    private function processDecline(Payment $payment, Booking $booking, array $payload): array
    {
        $reasonCode = $payload['reason_code'] ?? 'unknown';
        $message = $payload['message'] ?? 'Payment declined';

        $payment->update([
            'status' => 'declined',
            'decline_reason' => $reasonCode,
            'error_message' => $message,
        ]);

        AuditLog::log(
            $payment,
            'payment_declined',
            "Payment DECLINED: {$message}",
            'pending',
            'declined',
            ['reason_code' => $reasonCode, 'message' => $message]
        );

        // Mark booking as payment failed
        $bookingService = app(BookingService::class);
        $bookingService->markPaymentFailed($booking, $message);

        Log::warning('Payment declined', [
            'booking_id' => $booking->id,
            'reference' => $booking->reference,
            'reason' => $reasonCode,
        ]);

        return [
            'status' => 'declined',
            'booking_id' => $booking->id,
            'reason' => $reasonCode,
            'message' => $message,
        ];
    }

    /**
     * Initiate refund
     */
    public function refundPayment(Payment $payment, float $amount, string $reason): array
    {
        try {
            // Call CyberSource refund API
            $response = Http::withHeaders([
                'v-c-merchant-id' => $this->merchantId,
            ])->withBasicAuth($this->apiKey, $this->sharedSecret)
              ->post("{$this->apiEndpoint}/pts/v2/payments/{$payment->reference_number}/refunds", [
                'clientReferenceInformation' => [
                    'code' => $payment->booking->reference,
                ],
                'orderInformation' => [
                    'amountDetails' => [
                        'totalAmount' => (string) $amount,
                        'currency' => $payment->currency,
                    ],
                ],
            ]);

            if ($response->successful()) {
                $refundData = $response->json();
                
                $payment->update([
                    'status' => 'refunded',
                    'refund_transaction_id' => $refundData['id'] ?? null,
                    'refund_amount' => $amount,
                    'refunded_at' => now(),
                    'refund_reason' => $reason,
                ]);

                AuditLog::log(
                    $payment,
                    'payment_refunded',
                    "Refund processed: {$reason}",
                    'accepted',
                    'refunded',
                    ['amount' => $amount, 'refund_id' => $refundData['id'] ?? null]
                );

                return [
                    'success' => true,
                    'refund_id' => $refundData['id'] ?? null,
                ];
            }

            throw new \Exception($response->body());
        } catch (\Exception $e) {
            Log::error('Refund failed', [
                'payment_id' => $payment->id,
                'error' => $e->getMessage(),
            ]);

            throw $e;
        }
    }

    /**
     * Generate signature for secure fields
     */
    private function generateSignature(array $fields): string
    {
        $dataToSign = [];
        foreach (explode(',', $fields['signed_field_names']) as $field) {
            if (isset($fields[$field])) {
                $dataToSign[] = $field . '=' . $fields[$field];
            }
        }
        
        $dataString = implode(',', $dataToSign);
        return base64_encode(hash_hmac('sha256', $dataString, $this->sharedSecret, true));
    }
}
