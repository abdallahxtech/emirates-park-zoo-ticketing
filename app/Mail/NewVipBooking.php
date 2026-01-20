<?php

namespace App\Mail;

use App\Models\Booking;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;

class NewVipBooking extends Mailable
{
    use Queueable, SerializesModels;

    public function __construct(
        public Booking $booking
    ) {}

    public function envelope(): Envelope
    {
        return new Envelope(
            subject: 'VIP Booking Confirmed: ' . $this->booking->reference,
        );
    }

    public function content(): Content
    {
        return new Content(
            markdown: 'emails.team.new_vip_booking',
            with: [
                'booking' => $this->booking,
                'customer' => $this->booking->customer,
                'items' => $this->booking->items,
            ],
        );
    }
}
