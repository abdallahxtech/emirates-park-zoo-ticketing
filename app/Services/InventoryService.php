<?php

namespace App\Services;

use App\Models\Booking;
use App\Models\InventoryHold;
use App\Models\Ticket;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;

class InventoryService
{
    /**
     * Check if sufficient inventory is available
     */
    public function checkAvailability(int $ticketId, string|Carbon $visitDate, int $requestedQuantity): bool
    {
        $ticket = Ticket::findOrFail($ticketId);
        
        // If no daily capacity set, assume unlimited
        if (!$ticket->daily_capacity) {
            return true;
        }

        $visitDate = $visitDate instanceof Carbon ? $visitDate : Carbon::parse($visitDate);

        // Get confirmed bookings
        $confirmedCount = DB::table('booking_items')
            ->join('bookings', 'bookings.id', '=', 'booking_items.booking_id')
            ->where('booking_items.ticket_id', $ticketId)
            ->where('booking_items.visit_date', $visitDate->toDateString())
            ->whereIn('bookings.state', ['PAID', 'TICKETS_ISSUED', 'CONFIRMED'])
            ->whereNull('bookings.deleted_at')
            ->sum('booking_items.quantity');

        // Get active holds (not released and not expired)
        $heldCount = InventoryHold::where('ticket_id', $ticketId)
            ->where('visit_date', $visitDate->toDateString())
            ->where('is_released', false)
            ->where('expires_at', '>', now())
            ->sum('quantity');

        $available = $ticket->daily_capacity - ($confirmedCount + $heldCount);

        return $available >= $requestedQuantity;
    }

    /**
     * Get available quantity for a ticket on a date
     */
    public function getAvailableQuantity(int $ticketId, string|Carbon $visitDate): int
    {
        $ticket = Ticket::findOrFail($ticketId);
        
        // If no daily capacity set, return large number
        if (!$ticket->daily_capacity) {
            return 999;
        }

        $visitDate = $visitDate instanceof Carbon ? $visitDate : Carbon::parse($visitDate);

        // Get confirmed bookings
        $confirmedCount = DB::table('booking_items')
            ->join('bookings', 'bookings.id', '=', 'booking_items.booking_id')
            ->where('booking_items.ticket_id', $ticketId)
            ->where('booking_items.visit_date', $visitDate->toDateString())
            ->whereIn('bookings.state', ['PAID', 'TICKETS_ISSUED', 'CONFIRMED'])
            ->whereNull('bookings.deleted_at')
            ->sum('booking_items.quantity');

        // Get active holds
        $heldCount = InventoryHold::where('ticket_id', $ticketId)
            ->where('visit_date', $visitDate->toDateString())
            ->where('is_released', false)
            ->where('expires_at', '>', now())
            ->sum('quantity');

        return max(0, $ticket->daily_capacity - ($confirmedCount + $heldCount));
    }

    /**
     * Create inventory hold
     */
    public function createHold(
        Booking $booking,
        int $ticketId,
        string|Carbon $visitDate,
        int $quantity,
        Carbon $expiresAt
    ): InventoryHold {
        $visitDate = $visitDate instanceof Carbon ? $visitDate : Carbon::parse($visitDate);

        return InventoryHold::create([
            'booking_id' => $booking->id,
            'ticket_id' => $ticketId,
            'visit_date' => $visitDate->toDateString(),
            'quantity' => $quantity,
            'expires_at' => $expiresAt,
            'is_released' => false,
        ]);
    }

    /**
     * Release hold
     */
    public function releaseHold(InventoryHold $hold, string $reason = 'manual'): void
    {
        $hold->release($reason);
    }

    /**
     * Release all expired holds
     */
    public function releaseExpiredHolds(): int
    {
        $expiredHolds = InventoryHold::expired()->get();
        
        foreach ($expiredHolds as $hold) {
            $hold->release('expired');
        }

        return $expiredHolds->count();
    }

    /**
     * Get availability for multiple tickets on a date range
     */
    public function getAvailabilityForDateRange(array $ticketIds, Carbon $startDate, Carbon $endDate): array
    {
        $availability = [];
        
        $currentDate = $startDate->copy();
        while ($currentDate->lte($endDate)) {
            $dateKey = $currentDate->toDateString();
            $availability[$dateKey] = [];
            
            foreach ($ticketIds as $ticketId) {
                $availability[$dateKey][$ticketId] = [
                    'available' => $this->getAvailableQuantity($ticketId, $currentDate),
                    'ticket' => Ticket::find($ticketId),
                ];
            }
            
            $currentDate->addDay();
        }

        return $availability;
    }
}
