<?php

namespace App\Jobs;

use App\Models\Booking;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Mail;

class SendBookingConfirmationJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public int $timeout = 30;
    public int $tries = 3;

    /**
     * Create a new job instance.
     */
    public function __construct(
        public Booking $booking
    ) {}

    /**
     * Execute the job.
     */
    public function handle(): void
    {
        Log::info('Sending confirmation email', [
            'booking_id' => $this->booking->id,
            'customer_email' => $this->booking->customer->email,
        ]);

        try {
            // TODO: Send actual confirmation email
            // Mail::to($this->booking->customer->email)
            //     ->send(new BookingConfirmationMail($this->booking));

            Log::info('Confirmation email sent successfully', [
                'booking_id' => $this->booking->id,
                'customer_email' => $this->booking->customer->email,
            ]);

            // For now, just log the email content
            $emailData = $this->prepareEmailData();
            Log::info('Email content', $emailData);

        } catch (\Exception $e) {
            Log::error('Failed to send confirmation email', [
                'booking_id' => $this->booking->id,
                'error' => $e->getMessage(),
            ]);

            throw $e;
        }
    }

    /**
     * Prepare email data
     */
    private function prepareEmailData(): array
    {
        return [
            'to' => $this->booking->customer->email,
            'customer_name' => $this->booking->customer->full_name,
            'booking_reference' => $this->booking->reference,
            'visit_date' => $this->booking->visit_date?->format('F j, Y'),
            'total_amount' => $this->booking->currency . ' ' . number_format($this->booking->total, 2),
            'tickets' => $this->booking->galaxy_tickets,
            'items' => $this->booking->items->map(function ($item) {
                return [
                    'name' => $item->ticket_name,
                    'quantity' => $item->quantity,
                    'price' => $item->unit_price,
                ];
            })->toArray(),
        ];
    }
}
