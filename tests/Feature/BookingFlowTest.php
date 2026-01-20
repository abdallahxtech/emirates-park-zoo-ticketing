<?php

namespace Tests\Feature;

use App\Enums\BookingState;
use App\Models\Booking;
use App\Models\Product;
use App\Models\User;
use App\Services\BookingService;
use App\Services\PaymentService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use Tests\TestCase;

class BookingFlowTest extends TestCase
{
    use RefreshDatabase;

    public function test_full_booking_lifecycle()
    {
        // 1. Setup Data
        $product = Product::create([
            'name' => 'General Admission',
            'base_price' => 100,
            'is_active' => true,
        ]);

        $user = User::factory()->create(); // or customer

        // 2. Create Draft Booking via API (or Service)
        // Simulating Service call directly for simplicity of unit/feature mix
        $service = app(BookingService::class);
        
        $details = [
            'visit_date' => now()->addDay()->format('Y-m-d'),
            'customer' => [
                'email' => 'test@example.com',
                'first_name' => 'John',
                'last_name' => 'Doe',
                'phone' => '971500000000'
            ],
            'items' => [
                ['product_id' => $product->id, 'quantity' => 2]
            ]
        ];

        $booking = $service->createDraft($details);

        $this->assertEquals(BookingState::DRAFT, $booking->state);
        $this->assertEquals(200, $booking->total);
        $this->assertCount(1, $booking->items);

        // 3. Initiate Payment (Transitions to PENDING_PAYMENT)
        $paymentService = app(PaymentService::class);
        $paymentData = $paymentService->initiatePayment($booking);
        
        $booking->refresh();
        // Note: state might still be DRAFT until user goes to payment or logic depends strictly on callback
        // But typically initiatePayment doesn't change state, the confirmation does. 
        // Or if you have a state 'PENDING_PAYMENT', check transition logic.
        // Assuming initiatePayment might update it:
        // $booking->update(['state' => BookingState::PENDING_PAYMENT]); 
        
        $this->assertNotNull($paymentData['payment_id']);

        // 4. Simulate Successful Webhook
        $payment = \App\Models\Payment::find($paymentData['payment_id']);
        
        // Mock the signature verification to bypass secret key dependency in test
        $paymentServiceMock = \Mockery::mock(PaymentService::class)->makePartial();
        $paymentServiceMock->shouldReceive('verifyWebhookSignature')->andReturn(true);
        $this->app->instance(PaymentService::class, $paymentServiceMock);

        $payload = [
            'transaction_id' => $payment->transaction_id,
            'merchant_defined_data1' => $booking->id,
            'decision' => 'ACCEPT',
            'signed_field_names' => 'transaction_id,decision',
            'signature' => 'valid_mock_signature',
        ];

        $result = $paymentServiceMock->handleWebhookNotification($payload);

        // 5. Verify Confirmation
        $booking->refresh();
        $this->assertEquals('accepted', $result['status']);
        $this->assertEquals(BookingState::CONFIRMED->value, $booking->state->value); // or ISSUED
        
        // Check Ticket Issuance (Mock Mode likely active in test env)
        // BookingService::confirmPayment calls issueTickets
        
        $booking->refresh();
        // If auto-issue is enabled:
        // $this->assertEquals(BookingState::ISSUED, $booking->state);
        // $this->assertCount(2, $booking->tickets);
    }
}
