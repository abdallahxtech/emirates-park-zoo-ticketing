<?php

namespace App\Http\Controllers;

use App\Models\PaymentWebhook;
use App\Processors\PaymentWebhookProcessor;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use App\Services\CyberSourceService;

class PaymentWebhookController extends Controller
{
    public function __construct(
        protected CyberSourceService $cyberSourceService,
        protected PaymentWebhookProcessor $processor
    ) {}

    public function handle(Request $request)
    {
        // 1. Validate Signature
        if (!$this->cyberSourceService->verifySignature($request->all())) {
            Log::error('Invalid webhook signature attempt', $request->all());
            return response()->json(['error' => 'Invalid signature'], 400);
        }

        // 2. Identify Webhook (CyberSource doesn't always send a unique ID, so we might generate one from TransID)
        $webhookId = $request->input('transaction_id') ?? uniqid('wh_');
        $idempotencyKey = $webhookId . '_' . $request->input('decision');

        // 3. Log Webhook (Idempotency Check)
        $webhook = PaymentWebhook::firstOrCreate(
            ['idempotency_key' => $idempotencyKey],
            [
                'webhook_id' => $webhookId,
                'event_type' => 'payment.decision', // CyberSource is usually just decisions
                'payload' => $request->all(),
                'signature' => $request->input('signature'),
                'signature_valid' => true,
                'status' => 'pending',
            ]
        );

        if ($webhook->processed) {
            return response()->json(['message' => 'Already processed']);
        }

        // 4. Process Webhook
        try {
            $this->processor->process($webhook);
            return response()->json(['message' => 'Processed successfully']);
        } catch (\Exception $e) {
            Log::error('Webhook processing failed: ' . $e->getMessage());
            return response()->json(['error' => 'Processing failed'], 500);
        }
    }
}
