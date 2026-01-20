<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Booking;
use App\Services\BookingService;
use App\Services\PaymentService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;

class PaymentController extends Controller
{
    public function __construct(
        private PaymentService $paymentService,
        private BookingService $bookingService
    ) {}

    /**
     * Start payment process
     * 
     * POST /api/payments/start
     * {
     *   "booking_reference": "BOOK-2024-001234"
     * }
     */
    public function start(Request $request): JsonResponse
    {
        $validator = Validator::make($request->all(), [
            'booking_reference' => 'required|string|exists:bookings,reference',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'errors' => $validator->errors(),
            ], 422);
        }

        try {
            $booking = Booking::where('reference', $request->booking_reference)
                ->with(['customer', 'items'])
                ->firstOrFail();

            // Check if hold is expired
            if ($booking->is_hold_expired) {
                return response()->json([
                    'success' => false,
                    'message' => 'Booking hold has expired. Please create a new booking.',
                ], 410); // 410 Gone
            }

            // Transition to pending payment
            $booking = $this->bookingService->startPayment($booking);

            // Initialize payment with CyberSource
            $paymentData = $this->paymentService->initiatePayment($booking);

            return response()->json([
                'success' => true,
                'message' => 'Payment session created',
                'data' => [
                    'booking_reference' => $booking->reference,
                    'amount' => $booking->total,
                    'currency' => $booking->currency,
                    'payment_url' => $paymentData['payment_url'],
                    'secure_fields' => $paymentData['secure_fields'],
                    'transaction_id' => $paymentData['transaction_id'],
                ],
            ]);

        } catch (\InvalidArgumentException $e) {
            return response()->json([
                'success' => false,
                'message' => $e->getMessage(),
            ], 400);

        } catch (\Exception $e) {
            \Log::error('Failed to start payment', [
                'error' => $e->getMessage(),
                'booking_reference' => $request->booking_reference,
            ]);

            return response()->json([
                'success' => false,
                'message' => 'Failed to initialize payment. Please try again.',
            ], 500);
        }
    }
}
