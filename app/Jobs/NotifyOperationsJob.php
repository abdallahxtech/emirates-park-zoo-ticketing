<?php

namespace App\Jobs;

use App\Mail\NewVipBooking;
use App\Models\Booking;
use App\Models\User;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Mail;

class NotifyOperationsJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public function __construct(
        public Booking $booking
    ) {}

    public function handle(): void
    {
        // 1. Identify if this is a VIP booking
        $hasVip = $this->booking->items->contains(function ($item) {
            return $item->product && $item->product->type === 'vip';
        });

        if (!$hasVip) {
            return;
        }

        // 2. Find Staff to notify (Operations & Restaurant)
        // We can use a permission check or specific roles
        // Ideally, we'd have a setting for "Notification Emails", but for now retrieve by Role
        $recipients = User::whereHas('role', function ($q) {
            $q->whereIn('slug', ['super_admin', 'operations', 'restaurant']);
        })->pluck('email');

        // 3. Send Email
        foreach ($recipients as $email) {
            Mail::to($email)->send(new NewVipBooking($this->booking));
        }
    }
}
