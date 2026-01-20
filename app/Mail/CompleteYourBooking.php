<?php

namespace App\Mail;

use App\Models\Lead;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;

class CompleteYourBooking extends Mailable
{
    use Queueable, SerializesModels;

    public function __construct(public Lead $lead)
    {
    }

    public function envelope(): Envelope
    {
        return new Envelope(
            subject: 'Complete Your Booking at Emirates Park Zoo',
        );
    }

    public function content(): Content
    {
        return new Content(
            view: 'emails.complete-your-booking',
            with: [
                'name' => $this->lead->name ?? 'Guest',
                'url' => config('app.url') . '/checkout?lead_id=' . $this->lead->id,
            ],
        );
    }

    public function attachments(): array
    {
        return [];
    }
}
