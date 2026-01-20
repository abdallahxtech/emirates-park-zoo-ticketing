<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Booking;
use App\Models\BookingItem;
use App\Models\Product;
use App\Services\CyberSourceService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

class BookingController extends Controller
{
    public function __construct(
        protected CyberSourceService $paymentService
    ) {}

    public function store(Request $request)
    {
        $request->validate([
            'customer_name' => 'required|string',
            'customer_email' => 'required|email',
            'customer_phone' => 'required|string',
            'items' => 'required|array|min:1',
            'items.*.product_id' => 'required|exists:products,id',
            'items.*.quantity' => 'required|integer|min:1',
            'items.*.visit_date' => 'required|date',
            'items.*.time_slot' => 'nullable|string',
            'items.*.options' => 'nullable|array',
            'items.*.options.food_preference' => 'nullable|string',
            'items.*.options.dietary_notes' => 'nullable|string',
            'items.*.options.guest_names' => 'nullable|array',
            'utm' => 'nullable|array',
        ]);

        try {
            return DB::transaction(function () use ($request) {
                // 1. Calculate Totals
                $totalAmount = 0;
                $itemsToCreate = [];

                foreach ($request->items as $itemData) {
                    $product = Product::findOrFail($itemData['product_id']);
                    $subtotal = $product->base_price * $itemData['quantity'];
                    $totalAmount += $subtotal;

                    $itemsToCreate[] = [
                        'product' => $product,
                        'quantity' => $itemData['quantity'],
                        'subtotal' => $subtotal,
                        'visit_date' => $itemData['visit_date'],
                        'time_slot' => $itemData['time_slot'] ?? null,
                        'food_preference' => $itemData['options']['food_preference'] ?? null,
                        'dietary_notes' => $itemData['options']['dietary_notes'] ?? null,
                        'guest_names' => $itemData['options']['guest_names'] ?? null,
                    ];
                }

                // 2. Create Booking
                $booking = Booking::create([
                    'customer_name' => $request->customer_name,
                    'customer_email' => $request->customer_email,
                    'customer_phone' => $request->customer_phone,
                    'subtotal' => $totalAmount,
                    'total_amount' => $totalAmount, // Tax logic can be added here
                    'currency' => 'AED',
                    'state' => \App\Enums\BookingState::DRAFT,
                    'source' => 'online_api',
                ]);

                // 3. Create Items
                foreach ($itemsToCreate as $item) {
                    BookingItem::create([
                        'booking_id' => $booking->id,
                        'product_id' => $item['product']->id,
                        'quantity' => $item['quantity'],
                        'unit_price' => $item['product']->base_price,
                        'subtotal' => $item['subtotal'],
                        'visit_date' => $item['visit_date'],
                        'time_slot' => $item['time_slot'],
                        'food_preference' => $item['food_preference'],
                        'dietary_notes' => $item['dietary_notes'],
                        'guest_names' => $item['guest_names'],
                    ]);
                }

                // 4. Capture Lead (CRM)
                if ($request->has('utm')) {
                    \App\Models\Lead::create([
                         'email' => $request->customer_email,
                         'phone' => $request->customer_phone,
                         'name' => $request->customer_name,
                         'status' => 'converted', // Since they made a booking
                         'converted_booking_id' => $booking->id,
                         'utm_source' => $request->input('utm.source'),
                         'utm_medium' => $request->input('utm.medium'),
                         'utm_campaign' => $request->input('utm.campaign'),
                         'utm_content' => $request->input('utm.content'),
                         'utm_term' => $request->input('utm.term'),
                         'ip_address' => $request->ip(),
                         'landing_page_url' => $request->header('Referer'),
                    ]);
                }

                return response()->json([
                    'message' => 'Booking created successfully',
                    'booking_id' => $booking->booking_id,
                    'total_amount' => $booking->total_amount,
                    'payment_url' => route('api.bookings.payment', ['id' => $booking->id]), // Helper route
                ], 201);
            });
        } catch (\Exception $e) {
            return response()->json(['error' => 'Booking creation failed: ' . $e->getMessage()], 500);
        }
    }

    public function initiatePayment($id)
    {
        $booking = Booking::findOrFail($id);
        
        // Generate CyberSource signature and fields
        $paymentData = $this->paymentService->getPaymentFields($booking);

        return response()->json([
            'booking_id' => $booking->booking_id,
            'amount' => $booking->total_amount,
            'cybersource_payload' => $paymentData,
            'cybersource_url' => config('services.cybersource.api_url', 'https://testsecureacceptance.cybersource.com/pay'),
        ]);
    }

    public function status($id)
    {
        $booking = Booking::where('booking_id', $id)->orWhere('id', $id)->firstOrFail();
        
        return response()->json([
            'booking_id' => $booking->booking_id,
            'state' => $booking->state->value, // draft, confirmed, etc.
            'label' => $booking->state->label(),
        ]);
    }
}
