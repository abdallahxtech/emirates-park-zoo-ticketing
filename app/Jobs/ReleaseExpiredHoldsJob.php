<?php

namespace App\Jobs;

use App\Enums\BookingState;
use App\Models\Booking;
use App\Services\BookingService;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Log;

class ReleaseExpiredHoldsJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public int $timeout = 120;
    public int $tries = 3;

    /**
     * Execute the job.
     */
    public function handle(BookingService $bookingService): void
    {
        Log::info('Starting expired holds release job');

        try {
            // Find all bookings with expired holds
            $expiredBookings = Booking::where('state', BookingState::HOLD->value)
                ->where('hold_expires_at', '<', now())
                ->get();

            $count = 0;
            foreach ($expiredBookings as $booking) {
                try {
                    $bookingService->expireHold($booking);
                    $count++;
                    
                    Log::info('Expired hold released', [
                        'booking_id' => $booking->id,
                        'reference' => $booking->reference,
                    ]);
                } catch (\Exception $e) {
                    Log::error('Failed to release expired hold', [
                        'booking_id' => $booking->id,
                        'reference' => $booking->reference,
                        'error' => $e->getMessage(),
                    ]);
                }
            }

            Log::info('Expired holds release job completed', [
                'total_processed' => $count,
            ]);

        } catch (\Exception $e) {
            Log::error('Expired holds release job failed', [
                'error' => $e->getMessage(),
            ]);
            
            throw $e;
        }
    }
}
