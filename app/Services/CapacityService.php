<?php

namespace App\Services;

use App\Models\Product;
use App\Models\BookingItem;
use App\Enums\BookingState;
use Carbon\Carbon;

class CapacityService
{
    /**
     * Check if enough capacity exists for a product on a given date/time
     */
    public function check(Product $product, string $date, int $quantity, ?string $timeSlot = null): bool
    {
        if (!$product->has_capacity) {
            return true;
        }

        $dateObj = Carbon::parse($date);
        $totalCapacity = $product->getCapacityForDate($dateObj);

        // Calculate consumed capacity (Confirmed OR Active Holds)
        $query = BookingItem::query()
            ->where('product_id', $product->id)
            ->whereDate('visit_date', $dateObj)
            ->whereHas('booking', function ($q) {
                $q->where(function ($sub) {
                    // Include Confirmed bookings
                    $sub->where('state', BookingState::CONFIRMED);
                })->orWhere(function ($sub) {
                    // Include Drafts that haven't expired yet (Active Holds)
                    $sub->where('state', BookingState::DRAFT)
                        ->where('expires_at', '>', now());
                })->orWhere(function ($sub) {
                    // Pending Payment is effectively a hold too
                    $sub->where('state', BookingState::PENDING_PAYMENT);
                });
            });

        if ($product->is_time_slot_based && $timeSlot) {
            $query->where('time_slot', $timeSlot);
        }

        $consumed = $query->sum('quantity');
        $remaining = $totalCapacity - $consumed;

        return $remaining >= $quantity;
    }

    /**
     * Get remaining capacity count
     */
    public function getRemaining(Product $product, string $date, ?string $timeSlot = null): int
    {
        if (!$product->has_capacity) {
            return 9999;
        }
        
        // Similar logic to above, but return count
        $dateObj = Carbon::parse($date);
        $totalCapacity = $product->getCapacityForDate($dateObj);
        
        $query = BookingItem::query()
             ->where('product_id', $product->id)
             ->whereDate('visit_date', $dateObj)
             ->whereHas('booking', function ($q) {
                $q->where('state', BookingState::CONFIRMED)
                  ->orWhere(function ($sub) {
                      $sub->where('state', BookingState::DRAFT)->where('expires_at', '>', now());
                  })
                  ->orWhere('state', BookingState::PENDING_PAYMENT);
            });

        if ($product->is_time_slot_based && $timeSlot) {
            $query->where('time_slot', $timeSlot);
        }

        $consumed = $query->sum('quantity');
        
        return max(0, $totalCapacity - $consumed);
    }
}
