<?php

namespace App\Mail;

use App\Models\Booking;
use App\Models\BookingItem;
use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;

class CustomerVipConfirmed extends Mailable
{
    use Queueable, SerializesModels;

    public function __construct(
        public Booking $booking,
        public BookingItem $item // The specific VIP item
    ) {}

    public function envelope(): Envelope
    {
        return new Envelope(
            subject: '⭐ VIP Experience Confirmed — ' . $this->item->product->name . ' | ' . $this->booking->booking_id,
        );
    }

    public function content(): Content
    {
        return new Content(
            view: 'emails.customer-vip-confirmed',
        );
    }
}
