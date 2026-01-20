<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Booking;
use App\Models\BookingItem;
use App\Models\Product;
use App\Services\CapacityService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;

class HoldController extends Controller
{
    public function __construct(
        protected CapacityService $capacityService
    ) {}

    public function store(Request $request)
    {
        $request->validate([
            'items' => 'required|array|min:1',
            'items.*.product_id' => 'required|exists:products,id',
            'items.*.quantity' => 'required|integer|min:1',
            'items.*.visit_date' => 'required|date',
            'items.*.time_slot' => 'nullable|string',
        ]);

        return DB::transaction(function () use ($request) {
            $holdDuration = 15; // minutes
            $expiresAt = now()->addMinutes($holdDuration);
            $totalAmount = 0;
            $itemsToCreate = [];

            // 1. Check Capacity BEFORE creating hold
            foreach ($request->items as $itemData) {
                $product = Product::findOrFail($itemData['product_id']);
                
                $available = $this->capacityService->check(
                    $product, 
                    $itemData['visit_date'], 
                    $itemData['quantity'],
                    $itemData['time_slot'] ?? null
                );

                if (!$available) {
                    return response()->json([
                         'error' => "Not enough capacity for {$product->name}"
                    ], 422);
                }

                $subtotal = $product->base_price * $itemData['quantity'];
                $totalAmount += $subtotal;
                
                $itemsToCreate[] = [
                    'product' => $product,
                    'data' => $itemData,
                    'subtotal' => $subtotal
                ];
            }

            // 2. Create Draft Booking with Expiry
            $booking = Booking::create([
                'state' => \App\Enums\BookingState::DRAFT,
                'source' => 'online_hold',
                'expires_at' => $expiresAt,
                'total_amount' => $totalAmount,
                'currency' => 'AED',
                'customer_name' => 'Guest (Holding)', // Temporary
                'customer_email' => 'hold@temp.com',  // Temporary
            ]);

            foreach ($itemsToCreate as $item) {
                BookingItem::create([
                    'booking_id' => $booking->id,
                    'product_id' => $item['product']->id,
                    'quantity' => $item['data']['quantity'],
                    'visit_date' => $item['data']['visit_date'],
                    'time_slot' => $item['data']['time_slot'] ?? null,
                    'unit_price' => $item['product']->base_price,
                    'subtotal' => $item['subtotal'],
                ]);
            }

            return response()->json([
                'message' => 'Hold created successfully',
                'booking_id' => $booking->id, // Backend ID used for update later
                'reference' => $booking->booking_id,
                'expires_at' => $expiresAt->toIso8601String(),
                'expires_in_seconds' => $holdDuration * 60,
            ], 201);
        });
    }
}
