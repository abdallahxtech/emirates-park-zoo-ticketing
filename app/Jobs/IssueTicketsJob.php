<?php

namespace App\Jobs;

use App\Models\Booking;
use App\Services\GalaxyTicketingService;
use App\Mail\BookingConfirmed;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Log;

class IssueTicketsJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public function __construct(
        public Booking $booking
    ) {}

    public function handle(GalaxyTicketingService $ticketingService): void
    {
        Log::info("Starting ticket issuance for booking: " . $this->booking->booking_id);

        try {
            $ticketingService->issueTickets($this->booking);
            
            // Refresh relationship to include new tickets
            $this->booking->load('tickets');
            
            // Send email with tickets
            Mail::to($this->booking->customer_email)->send(new BookingConfirmed($this->booking));
            
            Log::info("Tickets issued and email sent for booking: " . $this->booking->booking_id);

        } catch (\Exception $e) {
            Log::error("Failed to issue tickets for booking {$this->booking->booking_id}: " . $e->getMessage());
            $this->fail($e);
        }
    }
}
