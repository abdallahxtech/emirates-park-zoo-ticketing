<?php

namespace App\Http\Controllers;

use App\Services\PaymentService;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\Log;

class WebhookController extends Controller
{
    public function __construct(
        private PaymentService $paymentService
    ) {}

    /**
     * Handle CyberSource webhook notification
     * 
     * CRITICAL: This is the ONLY trusted source for payment confirmation
     * Browser redirects are NOT trusted
     * 
     * POST /api/webhooks/cybersource
     */
    public function cybersource(Request $request): Response
    {
        Log::info('CyberSource webhook endpoint hit', [
            'ip' => $request->ip(),
            'payload_keys' => array_keys($request->all()),
        ]);

        try {
            // Extract payload
            $payload = $request->all();
            
            // Process webhook (idempotent)
            $result = $this->paymentService->handleWebhookNotification($payload);

            // CyberSource expects 200 OK response within 5 seconds
            Log::info('Webhook processed successfully', $result);
            
            return response('OK', 200);

        } catch (\Exception $e) {
            // Log error but still return 200 to prevent retries
            Log::error('Webhook processing error', [
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString(),
                'payload' => $request->all(),
            ]);

            // Still return 200 to acknowledge receipt
            // Investigate failed webhooks via logs
            return response('ERROR', 200);
        }
    }

    /**
     * Test endpoint for webhook verification
     * This can be used during integration testing
     */
    public function test(Request $request): Response
    {
        if (config('app.env') !== 'local') {
            abort(404);
        }

        Log::info('Test webhook received', $request->all());

        return response()->json([
            'message' => 'Test webhook received',
            'payload' => $request->all(),
        ]);
    }
}
