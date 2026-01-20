<?php

namespace App\Jobs;

use App\Models\Booking;
use App\Enums\BookingState;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;

class ExpireHeldBookingsJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public function handle(): void
    {
        // Cancel all bookings that are Draft and have expired
        Booking::where('state', BookingState::DRAFT)
            ->where('expires_at', '<', now())
            ->whereNotNull('expires_at')
            ->update(['state' => BookingState::CANCELLED]);
            
        // Note: BookingItem inventory release happens implicitly because CapacityService
        // checks active holds/confirmed only. Cancelled are ignored.
    }
}
