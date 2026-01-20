<?php

namespace App\Mail;

use App\Models\Booking;
use App\Models\BookingItem;
use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;

class OpsVipScheduled extends Mailable
{
    use Queueable, SerializesModels;

    public function __construct(
        public Booking $booking,
        public BookingItem $item
    ) {}

    public function envelope(): Envelope
    {
        return new Envelope(
            subject: 'ACTION REQUIRED: VIP Scheduled — ' . $this->item->product->name,
        );
    }

    public function content(): Content
    {
        return new Content(
            view: 'emails.ops-vip-scheduled',
        );
    }
}
